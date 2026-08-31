<?php
require_once __DIR__ . '/../../config/conexion.php';

// Consultas relacionadas con los productos de boda.
class Producto {
    private PDO $bd;

    public function __construct() {
        $this->bd = Conexion::obtener();
    }

    public function listarDisponibles(): array {
        // Los productos activos se muestran aunque no tengan stock para indicar que están agotados.
        return $this->bd->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC")->fetchAll();
    }

    public function listarTodos(): array {
        return $this->bd->query('SELECT * FROM productos ORDER BY id DESC')->fetchAll();
    }

    public function buscar(int $id): array|false {
        $consulta = $this->bd->prepare('SELECT * FROM productos WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch();
    }
}
