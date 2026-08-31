<?php
// Funciones comunes de sesión y permisos.
class Autenticacion {
    public static function iniciado(): bool {
        return isset($_SESSION['usuario']['id']);
    }

    public static function esAdministrador(): bool {
        return self::iniciado() && ($_SESSION['usuario']['rol'] ?? '') === 'administrador';
    }

    public static function exigirInicio(): void {
        if (!self::iniciado()) {
            header('Location: ' . URL_BASE . '/inicio');
            exit;
        }
    }

    public static function exigirAdministrador(): void {
        if (!self::esAdministrador()) {
            header('Location: ' . URL_BASE . '/inicio');
            exit;
        }
    }

    // Token que evita que otro sitio envíe formularios administrativos en nombre del usuario.
    public static function tokenFormulario(): string {
        $_SESSION['token_formulario'] ??= bin2hex(random_bytes(32));
        return $_SESSION['token_formulario'];
    }

    public static function validarToken(?string $token): bool {
        return isset($_SESSION['token_formulario']) && is_string($token) && hash_equals($_SESSION['token_formulario'], $token);
    }
}
