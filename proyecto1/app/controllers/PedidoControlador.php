<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/RegistroAdmin.php';

class PedidoControlador {
    private Pedido $pedidos;
    private RegistroAdmin $registro;

    public function __construct() {
        // RETIRAMOS Autenticacion::exigirAdministrador() de aquí para permitir excepciones en los métodos
        $this->pedidos = new Pedido();
        $this->registro = new RegistroAdmin();
    }

    // MÉTODOS EXCLUSIVOS DE ADMINISTRACIÓN (Llevan la protección individualmente)

    public function index(): void {
        Autenticacion::exigirAdministrador(); // PROTEGIDO
        $pedidos = $this->pedidos->listarAdministracion();
        require __DIR__ . '/../views/pedidos_admin.php';
    }

    public function detalle(int $id): void {
        Autenticacion::exigirAdministrador(); // PROTEGIDO
        $detalle = $this->pedidos->detalleAdministracion($id);
        if (!$detalle) { header('Location: ' . URL_BASE . '/pedido'); exit; }
        require __DIR__ . '/../views/detalle_pedido_admin.php';
    }

    public function estado(int $id): void {
        Autenticacion::exigirAdministrador(); // PROTEGIDO
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) { 
            http_response_code(403); 
            exit('Solicitud no válida.'); 
        }
        try {
            $estado = $_POST['estado'] ?? '';
            $this->pedidos->actualizarEstado($id, $estado);
            $this->registro->guardar($_SESSION['usuario']['id'], 'Cambió estado de pedido', 'Pedido #' . $id . ' a ' . $estado);
            $_SESSION['mensaje_admin'] = 'Estado del pedido actualizado.';
        } catch (Throwable $error) { 
            $_SESSION['mensaje_admin'] = $error->getMessage(); 
        }
        header('Location: ' . URL_BASE . '/pedido/detalle/' . $id);
    }
    // MÉTODO EXCEPCIÓN: ACCESIBLE POR EL CLIENTE LOGUEADO
    public function mispedidos(): void
    {
        // 1. Protección de ruta: Solo exige que haya iniciado sesión (cualquier cliente)
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/inicio');
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario']['id'];
        $pedidosCliente = [];

        try {
            // 2. REUTILIZACIÓN DEL MODELO: Invocamos de forma limpia la nueva función
            $pedidosCliente = $this->pedidos->listarPorUsuario($usuario_id);
            
        } catch (Throwable $e) {
            $pedidosCliente = [];
        }

        // 3. Cargamos la vista de cara al cliente
        require __DIR__ . '/../views/pedidos.php';
    }

}
