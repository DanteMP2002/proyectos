<?php
require_once __DIR__ . '/../../config/conexion.php';

// Bitácora mínima para saber qué administrador realizó cambios importantes.
class RegistroAdmin {
    private PDO $bd;

    public function __construct() { $this->bd = Conexion::obtener(); }

    public function guardar(int $usuarioId, string $accion, string $detalle): void {
        $consulta = $this->bd->prepare('INSERT INTO registros_admin (usuario_id, accion, detalle) VALUES (:usuario, :accion, :detalle)');
        $consulta->execute(['usuario' => $usuarioId, 'accion' => $accion, 'detalle' => $detalle]);
    }

    public function ultimos(int $limite = 8): array {
        $consulta = $this->bd->prepare('SELECT r.*, u.nombre FROM registros_admin r INNER JOIN usuarios u ON u.id = r.usuario_id ORDER BY r.creado_en DESC LIMIT :limite');
        $consulta->bindValue('limite', $limite, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll();
    }
}
