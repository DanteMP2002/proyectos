<?php
require_once __DIR__ . '/../models/Usuario.php';

// Autentica solo cuando la persona decide comprar o entrar al panel.
class LoginControlador {
    private function respuesta(array $datos, int $estado = 200): never {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit;
    }

    private function iniciarSesion(array $usuario): void {
        session_regenerate_id(true);
        $_SESSION['usuario'] = ['id' => (int)$usuario['id'], 'nombre' => $usuario['nombre'], 'rol' => $usuario['rol']];
    }

    public function autenticar(): void {
        $usuario = (new Usuario())->buscarPorCorreo(trim($_POST['correo'] ?? ''));
        if (!$usuario || !password_verify($_POST['clave'] ?? '', $usuario['clave'])) $this->respuesta(['ok' => false, 'mensaje' => 'Correo o contraseña incorrectos.'], 422);
        $this->iniciarSesion($usuario);
        $this->respuesta(['ok' => true, 'mensaje' => 'Sesión iniciada correctamente.']);
    }

    public function registrar(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $clave = $_POST['clave'] ?? '';
        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($clave) < 6) $this->respuesta(['ok' => false, 'mensaje' => 'Completa los datos. La contraseña debe tener 6 caracteres o más.'], 422);
        $modelo = new Usuario();
        if ($modelo->buscarPorCorreo($correo)) $this->respuesta(['ok' => false, 'mensaje' => 'Ese correo ya está registrado.'], 422);
        $modelo->registrarCliente($nombre, $correo, $clave);
        $this->iniciarSesion($modelo->buscarPorCorreo($correo));
        $this->respuesta(['ok' => true, 'mensaje' => 'Tu cuenta fue creada.']);
    }

    public function administrador(): void {
        $usuario = (new Usuario())->buscarPorCorreo(trim($_POST['correo'] ?? ''));
        $claveCorrecta = $usuario && password_verify($_POST['clave'] ?? '', $usuario['clave']);
        // El rol se valida en la base de datos: no basta con conocer un correo y contraseña cualquiera.
        if (!$claveCorrecta || $usuario['rol'] !== 'administrador') $this->respuesta(['ok' => false, 'mensaje' => 'No se pudo validar el acceso de administrador.'], 403);
        $this->iniciarSesion($usuario);
        $this->respuesta(['ok' => true, 'redirigir' => URL_BASE . '/admin']);
    }

    public function salir(): void {
        unset($_SESSION['usuario']);
        header('Location: ' . URL_BASE . '/inicio');
    }
}
