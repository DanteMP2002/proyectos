<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Pedido.php';

// El checkout sí exige sesión: se necesita saber quién hizo el pedido.
class CheckoutControlador {
    public function iniciar(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!Autenticacion::iniciado()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'requiere_acceso' => true]);
            return;
        }
        echo json_encode(['ok' => true, 'redirigir' => URL_BASE . '/checkout/formulario']);
    }

    public function formulario(): void {
        Autenticacion::exigirInicio();
        if (empty($_SESSION['carrito'])) { header('Location: ' . URL_BASE . '/inicio'); exit; }
        $carrito = array_values($_SESSION['carrito']);
        $total = array_sum(array_column($carrito, 'subtotal'));
        require __DIR__ . '/../views/checkout.php';
    }

    public function confirmar(): void {
        Autenticacion::exigirInicio();
        if (empty($_SESSION['carrito'])) { header('Location: ' . URL_BASE . '/inicio'); exit; }
        $metodo = in_array($_POST['metodo_pago'] ?? '', ['yape', 'tarjeta', 'transferencia'], true) ? $_POST['metodo_pago'] : 'tarjeta';
        try {
            $pedidoId = (new Pedido())->crear((int)$_SESSION['usuario']['id'], array_values($_SESSION['carrito']), $metodo);
            $_SESSION['carrito'] = [];
            $_SESSION['mensaje_compra'] = 'Pedido #' . $pedidoId . ' registrado correctamente.';
        } catch (Throwable $error) { $_SESSION['mensaje_compra'] = $error->getMessage(); }
        header('Location: ' . URL_BASE . '/inicio');
    }
}
