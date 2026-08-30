<?php
require_once __DIR__ . '/../../config/conexion.php';

// Registra compras y descuenta el stock de forma atómica.
class Pedido {
    private PDO $bd;

    public function __construct() { $this->bd = Conexion::obtener(); }

    public function crear(int $usuarioId, array $carrito, string $metodoPago): int {
        $this->bd->beginTransaction();
        try {
            $total = 0;
            $lineas = [];
            $buscar = $this->bd->prepare('SELECT id, nombre, precio, stock, activo FROM productos WHERE id = :id FOR UPDATE');
            // Se vuelve a comprobar precio y stock: el carrito del navegador no es confiable.
            foreach ($carrito as $item) {
                $buscar->execute(['id' => (int)$item['id']]);
                $producto = $buscar->fetch();
                if (!$producto || !$producto['activo'] || $producto['stock'] < $item['cantidad']) throw new RuntimeException('Uno de los productos ya no tiene stock suficiente.');
                $subtotal = (float)$producto['precio'] * (int)$item['cantidad'];
                $total += $subtotal;
                $lineas[] = ['producto' => $producto, 'cantidad' => (int)$item['cantidad'], 'subtotal' => $subtotal];
            }
            $codigo = 'BOD-' . date('YmdHis') . '-' . random_int(100, 999);
            $crearPedido = $this->bd->prepare("INSERT INTO pedidos (codigo, usuario_id, total, metodo_pago, estado) VALUES (:codigo, :usuario, :total, :metodo, 'pendiente')");
            $crearPedido->execute(['codigo' => $codigo, 'usuario' => $usuarioId, 'total' => $total, 'metodo' => $metodoPago]);
            $pedidoId = (int)$this->bd->lastInsertId();
            $crearDetalle = $this->bd->prepare('INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (:pedido, :producto, :cantidad, :precio, :subtotal)');
            $descontar = $this->bd->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id = :id');
            foreach ($lineas as $linea) {
                $crearDetalle->execute(['pedido' => $pedidoId, 'producto' => $linea['producto']['id'], 'cantidad' => $linea['cantidad'], 'precio' => $linea['producto']['precio'], 'subtotal' => $linea['subtotal']]);
                $descontar->execute(['cantidad' => $linea['cantidad'], 'id' => $linea['producto']['id']]);
            }
            $this->bd->commit();
            return $pedidoId;
        } catch (Throwable $error) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            throw $error;
        }
    }
}
