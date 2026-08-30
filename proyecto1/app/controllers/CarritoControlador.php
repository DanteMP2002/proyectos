<?php
require_once __DIR__ . '/../models/Producto.php';

// Carrito de invitado: vive en la sesión, no depende de una cuenta.
class CarritoControlador {
    private Producto $productos;

    public function __construct() {
        $this->productos = new Producto();
        $_SESSION['carrito'] ??= [];
    }

    private function responder(array $datos, int $estado = 200): never {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit;
    }

    private function obtenerResumen(): array {
        $items = array_values($_SESSION['carrito']);
        return ['items' => $items, 'total' => array_sum(array_column($items, 'subtotal')), 'cantidad' => array_sum(array_column($items, 'cantidad'))];
    }

    public function resumen(): void { $this->responder(['ok' => true, 'carrito' => $this->obtenerResumen()]); }

    public function agregar(int $id): void {
        $producto = $this->productos->buscar($id);
        if (!$producto || !$producto['activo'] || $producto['stock'] < 1) $this->responder(['ok' => false, 'mensaje' => 'Este producto no está disponible.'], 422);
        $actual = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
        if ($actual >= $producto['stock']) $this->responder(['ok' => false, 'mensaje' => 'Alcanzaste el stock disponible.'], 422);
        $cantidad = $actual + 1;
        $_SESSION['carrito'][$id] = ['id' => (int)$producto['id'], 'nombre' => $producto['nombre'], 'precio' => (float)$producto['precio'], 'imagen' => $producto['imagen'], 'cantidad' => $cantidad, 'subtotal' => (float)$producto['precio'] * $cantidad];
        $this->responder(['ok' => true, 'mensaje' => 'Producto añadido al carrito.', 'carrito' => $this->obtenerResumen()]);
    }

    public function cambiar(int $id): void {
        $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));
        if (!isset($_SESSION['carrito'][$id])) $this->responder(['ok' => false], 404);
        $producto = $this->productos->buscar($id);
        if (!$producto || $cantidad === 0) unset($_SESSION['carrito'][$id]);
        else {
            $cantidad = min($cantidad, (int)$producto['stock']);
            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
            $_SESSION['carrito'][$id]['precio'] = (float)$producto['precio'];
            $_SESSION['carrito'][$id]['subtotal'] = (float)$producto['precio'] * $cantidad;
        }
        $this->responder(['ok' => true, 'carrito' => $this->obtenerResumen()]);
    }

    public function quitar(int $id): void {
        unset($_SESSION['carrito'][$id]);
        $this->responder(['ok' => true, 'carrito' => $this->obtenerResumen()]);
    }
}
