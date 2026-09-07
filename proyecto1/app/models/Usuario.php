<?php
require_once __DIR__ . '/../../config/conexion.php';

// Registro y validación de cuentas de clientes y administradores.
class Usuario {
    private PDO $bd;

    public function __construct() {
        $this->bd = Conexion::obtener();
    }

    public function buscarPorCorreo(string $correo): array|false {
        // Incluye la clave porque este método se usa exclusivamente al autenticar.
        $consulta = $this->bd->prepare('SELECT id, nombre, correo, clave, rol, creado_en FROM usuarios WHERE correo = :correo LIMIT 1');
        $consulta->execute(['correo' => $correo]);
        return $consulta->fetch();
    }

    /** Indica si el correo ya pertenece a una cuenta registrada. */
    public function correoExiste(string $correo): bool {
        return $this->buscarPorCorreo($correo) !== false;
    }

    public function registrarCliente(string $nombre, string $correo, string $clave): bool {
        $consulta = $this->bd->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (:nombre, :correo, :clave, 'cliente')");
        return $consulta->execute(['nombre' => $nombre, 'correo' => $correo, 'clave' => password_hash($clave, PASSWORD_DEFAULT)]);
    }
}
