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
    // Esta función responderá a la URL: /pedido/mispedidos
    public function mispedidos()
    {
        // 1. Protección de ruta: si no está logueado, va al inicio
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/inicio');
            exit;
        }

        $usuario_id = $_SESSION['usuario']['id'];
        $pedidosCliente = [];

        try {
            // 2. Consulta segura usando tu clase Conexion
            $conexion = Conexion::obtener();
            $stmt = $conexion->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC");
            $stmt->execute([$usuario_id]);
            $pedidosCliente = $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $pedidosCliente = [];
        }

        // 3. Cargamos la vista pública del cliente (ajusta la ruta según tus vistas)
        // Normalmente las guardas en /app/views/ o similar.
        require_once __DIR__ . '/../views/pedidos.php';
    }
}
