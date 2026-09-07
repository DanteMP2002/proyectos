<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Pedido.php';

/** Presenta y confirma la compra de los productos guardados en sesión. */
class CheckoutControlador
{
    private Pedido $pedidos;

    public function __construct()
    {
        $this->pedidos = new Pedido();
    }

    private function volverAInicio(): never
    {
        header('Location: ' . URL_BASE . '/inicio');
        exit;
    }

    private function carritoActual(): array
    {
        return array_values($_SESSION['carrito'] ?? []);
    }

    public function iniciar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Autenticacion::iniciado()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'requiere_acceso' => true]);
            return;
        }

        echo json_encode(['ok' => true, 'redirigir' => URL_BASE . '/checkout/formulario']);
    }

    public function formulario(): void
    {
        Autenticacion::exigirInicio();
        $carrito = $this->carritoActual();

        if ($carrito === []) {
            $this->volverAInicio();
        }

        $total = array_sum(array_column($carrito, 'subtotal'));
        require __DIR__ . '/../views/checkout.php';
    }

    public function confirmar(): void
    {
        Autenticacion::exigirInicio();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }

        $carrito = $this->carritoActual();

        if ($carrito === []) {
            $this->volverAInicio();
        }

        $metodosPermitidos = ['yape', 'tarjeta', 'transferencia'];
        $metodoPago = $_POST['metodo_pago'] ?? '';

        if (!in_array($metodoPago, $metodosPermitidos, true)) {
            $_SESSION['mensaje_compra'] = 'Selecciona un método de pago válido.';
            $this->volverAInicio();
        }

        try {
            $pedidoId = $this->pedidos->crear((int) $_SESSION['usuario']['id'], $carrito, $metodoPago);
            $_SESSION['carrito'] = [];
            $_SESSION['mensaje_compra'] = 'Pedido #' . $pedidoId . ' registrado correctamente.';
        } catch (Throwable $error) {
            $_SESSION['mensaje_compra'] = $error->getMessage();
        }

        $this->volverAInicio();
    }
}
