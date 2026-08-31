<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/RegistroAdmin.php';

// Gestión de pedidos exclusiva para administración.
class PedidoControlador {
    private Pedido $pedidos;
    private RegistroAdmin $registro;

    public function __construct() {
        Autenticacion::exigirAdministrador();
        $this->pedidos = new Pedido();
        $this->registro = new RegistroAdmin();
    }

    public function index(): void {
        $pedidos = $this->pedidos->listarAdministracion();
        require __DIR__ . '/../views/pedidos_admin.php';
    }

    public function detalle(int $id): void {
        $detalle = $this->pedidos->detalleAdministracion($id);
        if (!$detalle) { header('Location: ' . URL_BASE . '/pedido'); exit; }
        require __DIR__ . '/../views/detalle_pedido_admin.php';
    }

    public function estado(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) { http_response_code(403); exit('Solicitud no válida.'); }
        try {
            $estado = $_POST['estado'] ?? '';
            $this->pedidos->actualizarEstado($id, $estado);
            $this->registro->guardar($_SESSION['usuario']['id'], 'Cambió estado de pedido', 'Pedido #' . $id . ' a ' . $estado);
            $_SESSION['mensaje_admin'] = 'Estado del pedido actualizado.';
        } catch (Throwable $error) { $_SESSION['mensaje_admin'] = $error->getMessage(); }
        header('Location: ' . URL_BASE . '/pedido/detalle/' . $id);
    }
}
