<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Autenticacion.php';

/** Autentica clientes y administradores, y administra su sesión. */
class LoginControlador
{
    private Usuario $usuarios;

    public function __construct()
    {
        $this->usuarios = new Usuario();
    }

    /** Envía una respuesta JSON y termina la petición AJAX. */
    private function responder(array $datos, int $estado = 200): never
    {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit;
    }

    /** Guarda en sesión solo los datos necesarios para identificar al usuario. */
    private function iniciarSesion(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol'],
        ];
    }

    private function correoRecibido(): string
    {
        return mb_strtolower(trim($_POST['correo'] ?? ''));
    }

    /** Bloquea envíos ajenos al sitio antes de procesar credenciales. */
    private function exigirFormularioSeguro(): void
    {
        $esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        $tokenValido = Autenticacion::validarToken($_POST['token'] ?? null);

        if (!$esPost || !$tokenValido) {
            $this->responder(['ok' => false, 'mensaje' => 'Solicitud no válida.'], 403);
        }
    }

    public function autenticar(): void
    {
        $this->exigirFormularioSeguro();
        $usuario = $this->usuarios->buscarPorCorreo($this->correoRecibido());
        $claveCorrecta = $usuario && password_verify($_POST['clave'] ?? '', $usuario['clave']);

        if (!$claveCorrecta) {
            $this->responder(['ok' => false, 'mensaje' => 'Correo o contraseña incorrectos.'], 422);
        }

        $this->iniciarSesion($usuario);
        $this->responder(['ok' => true, 'mensaje' => 'Sesión iniciada correctamente.']);
    }

    public function registrar(): void
    {
        $this->exigirFormularioSeguro();
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = $this->correoRecibido();
        $clave = $_POST['clave'] ?? '';

        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($clave) < 6) {
            $this->responder(['ok' => false, 'mensaje' => 'Completa los datos. La contraseña debe tener 6 caracteres o más.'], 422);
        }

        if ($this->usuarios->correoExiste($correo)) {
            $this->responder(['ok' => false, 'mensaje' => 'Ese correo ya está registrado.'], 422);
        }

        $this->usuarios->registrarCliente($nombre, $correo, $clave);
        $usuarioNuevo = $this->usuarios->buscarPorCorreo($correo);
        $this->iniciarSesion($usuarioNuevo);
        $this->responder(['ok' => true, 'mensaje' => 'Tu cuenta fue creada.']);
    }

    public function administrador(): void
    {
        $this->exigirFormularioSeguro();
        $usuario = $this->usuarios->buscarPorCorreo($this->correoRecibido());
        $esAdministrador = $usuario
            && $usuario['rol'] === 'administrador'
            && password_verify($_POST['clave'] ?? '', $usuario['clave']);

        if (!$esAdministrador) {
            $this->responder(['ok' => false, 'mensaje' => 'No se pudo validar el acceso de administrador.'], 403);
        }

        $this->iniciarSesion($usuario);
        $this->responder(['ok' => true, 'redirigir' => URL_BASE . '/admin']);
    }

    public function salir(): void
    {
        unset($_SESSION['usuario']);
        header('Location: ' . URL_BASE . '/inicio');
        exit;
    }
}
