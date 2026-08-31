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

    // Crea un producto desde el panel de administración.
    public function crear(array $datos): int {
        $consulta = $this->bd->prepare('INSERT INTO productos (nombre, categoria, descripcion, precio, stock, imagen, activo) VALUES (:nombre, :categoria, :descripcion, :precio, :stock, :imagen, :activo)');
        $consulta->execute($datos);
        return (int)$this->bd->lastInsertId();
    }

    // Actualiza datos, stock, visibilidad e imagen de un producto existente.
    public function actualizar(int $id, array $datos): bool {
        $datos['id'] = $id;
        $consulta = $this->bd->prepare('UPDATE productos SET nombre = :nombre, categoria = :categoria, descripcion = :descripcion, precio = :precio, stock = :stock, imagen = :imagen, activo = :activo WHERE id = :id');
        return $consulta->execute($datos);
    }

    public function resumenAdministrativo(): array {
        return $this->bd->query('SELECT COUNT(*) AS productos, COALESCE(SUM(stock), 0) AS unidades, COALESCE(SUM(activo = 1), 0) AS visibles FROM productos')->fetch();
    }
}
