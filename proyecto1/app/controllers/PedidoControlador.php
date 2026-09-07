<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Pedido.php';

/** Gestiona historial, detalle y estado de pedidos según el rol del usuario. */
class PedidoControlador
{
    private Pedido $pedidos;

    public function __construct()
    {
        $this->pedidos = new Pedido();
    }

    public function index(): void
    {
        Autenticacion::exigirAdministrador();
        $pedidos = $this->pedidos->listarAdministracion();
        require __DIR__ . '/../views/pedidos_admin.php';
    }

    public function detalle(int $id): void
    {
        Autenticacion::exigirInicio();
        $detalle = $this->pedidos->detalleAdministracion($id);

        if (!$detalle) {
            header('Location: ' . URL_BASE . '/pedido/mispedidos');
            exit;
        }

        // Un cliente solo puede consultar pedidos asociados a su propio usuario.
        $esPropietario = (int) $detalle['pedido']['usuario_id'] === (int) $_SESSION['usuario']['id'];
        if (!Autenticacion::esAdministrador() && !$esPropietario) {
            header('Location: ' . URL_BASE . '/pedido/mispedidos');
            exit;
        }

        require __DIR__ . '/../views/detalle_pedido_admin.php';
    }

    public function estado(int $id): void
    {
        Autenticacion::exigirAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }

        try {
            $this->pedidos->actualizarEstado($id, $_POST['estado'] ?? '');
            $_SESSION['mensaje_admin'] = 'Estado del pedido actualizado.';
        } catch (Throwable $error) {
            $_SESSION['mensaje_admin'] = $error->getMessage();
        }

        header('Location: ' . URL_BASE . '/pedido/detalle/' . $id);
        exit;
    }

    public function mispedidos(): void
    {
        Autenticacion::exigirInicio();
        $pedidosCliente = $this->pedidos->listarPorUsuario((int) $_SESSION['usuario']['id']);
        require __DIR__ . '/../views/pedidos.php';
    }
}
