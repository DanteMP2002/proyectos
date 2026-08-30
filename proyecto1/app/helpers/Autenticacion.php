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
}
