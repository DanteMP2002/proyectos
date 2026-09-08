<?php
require_once __DIR__ . '/../../config/conexion.php';

/** Consultas de productos y de sus imágenes asociadas. */
class Producto
{
    private PDO $bd;

    public function __construct()
    {
        $this->bd = Conexion::obtener();
    }

    /** Consulta base que agrega la ruta de la imagen marcada como portada. */
    private function consultaConPortada(): string
    {
        return 'SELECT p.*, portada.ruta_imagen AS imagen
            FROM productos p
            LEFT JOIN imagenes_producto portada
                ON portada.producto_id = p.id AND portada.es_principal = 1';
    }

    public function listarDisponibles(): array
    {
        $productos = $this->bd->query($this->consultaConPortada() . ' WHERE p.activo = 1 ORDER BY p.id DESC')->fetchAll();
        return $this->adjuntarImagenes($productos);
    }

    public function listarTodos(): array
    {
        $productos = $this->bd->query($this->consultaConPortada() . ' ORDER BY p.id DESC')->fetchAll();
        return $this->adjuntarImagenes($productos);
    }

    /** Añade todas las rutas de galería para las tarjetas y el modal del catálogo. */
    private function adjuntarImagenes(array $productos): array
    {
        if (!$productos) {
            return $productos;
        }

        $ids = array_map(static fn(array $producto): int => (int) $producto['id'], $productos);
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $consulta = $this->bd->prepare("SELECT producto_id, ruta_imagen, es_principal FROM imagenes_producto WHERE producto_id IN ($marcadores) ORDER BY producto_id ASC, (es_principal = 1) DESC, id ASC");
        $consulta->execute($ids);

        $imagenesPorProducto = [];
        foreach ($consulta->fetchAll() as $imagen) {
            $imagenesPorProducto[(int) $imagen['producto_id']][] = [
                'ruta_imagen' => $imagen['ruta_imagen'],
                'es_principal' => (int) $imagen['es_principal'] === 1,
            ];
        }

        foreach ($productos as &$producto) {
            $producto['imagenes'] = $imagenesPorProducto[(int) $producto['id']] ?? [];
        }
        unset($producto);

        return $productos;
    }

    public function buscar(int $id): array|false
    {
        $consulta = $this->bd->prepare($this->consultaConPortada() . ' WHERE p.id = :id LIMIT 1');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch();
    }

    /** Devuelve portada y fotos adicionales para el formulario de edición. */
    public function listarImagenes(int $productoId): array
    {
        $consulta = $this->bd->prepare('SELECT id, ruta_imagen, es_principal FROM imagenes_producto WHERE producto_id = :producto ORDER BY es_principal DESC, id ASC');
        $consulta->execute(['producto' => $productoId]);
        return $consulta->fetchAll();
    }

    public function estaDisponible(array|false $producto): bool
    {
        return $producto !== false && (int) $producto['activo'] === 1 && (int) $producto['stock'] > 0;
    }

    /** Crea solo los datos de productos; las rutas pertenecen a imagenes_producto. */
    public function crear(array $datos): int
    {
        $consulta = $this->bd->prepare('INSERT INTO productos (nombre, categoria, descripcion, precio, stock, activo) VALUES (:nombre, :categoria, :descripcion, :precio, :stock, :activo)');
        $consulta->execute($datos);
        return (int) $this->bd->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $datos['id'] = $id;
        $consulta = $this->bd->prepare('UPDATE productos SET nombre = :nombre, categoria = :categoria, descripcion = :descripcion, precio = :precio, stock = :stock, activo = :activo WHERE id = :id');
        return $consulta->execute($datos);
    }

    /** Guarda la portada y las fotos adicionales en la tabla independiente. */
    public function guardarImagenes(int $productoId, ?string $portada, array $adicionales): void
    {
        if ($portada !== null) {
            // La restricción única permite una sola fila con es_principal = 1.
            $quitarPortada = $this->bd->prepare('UPDATE imagenes_producto SET es_principal = NULL WHERE producto_id = :producto AND es_principal = 1');
            $quitarPortada->execute(['producto' => $productoId]);
            $this->insertarImagen($productoId, $portada, 1);
        }

        foreach ($adicionales as $ruta) {
            $this->insertarImagen($productoId, $ruta, null);
        }
    }

    /** Reemplaza una imagen concreta y confirma que pertenece al producto editado. */
    public function reemplazarImagen(int $productoId, int $imagenId, string $ruta): string|false
    {
        $consulta = $this->bd->prepare('SELECT ruta_imagen FROM imagenes_producto WHERE id = :imagen AND producto_id = :producto LIMIT 1');
        $consulta->execute(['imagen' => $imagenId, 'producto' => $productoId]);
        $imagenAnterior = $consulta->fetchColumn();
        if ($imagenAnterior === false) {
            return false;
        }

        $actualizacion = $this->bd->prepare('UPDATE imagenes_producto SET ruta_imagen = :ruta WHERE id = :imagen AND producto_id = :producto');
        $actualizacion->execute(['ruta' => $ruta, 'imagen' => $imagenId, 'producto' => $productoId]);
        return (string) $imagenAnterior;
    }

    /** Marca una imagen existente como portada y deja las demás como adicionales. */
    public function establecerPortada(int $productoId, int $imagenId): bool
    {
        $consulta = $this->bd->prepare('SELECT id FROM imagenes_producto WHERE id = :imagen AND producto_id = :producto LIMIT 1');
        $consulta->execute(['imagen' => $imagenId, 'producto' => $productoId]);
        if ($consulta->fetchColumn() === false) {
            return false;
        }

        $quitarActual = $this->bd->prepare('UPDATE imagenes_producto SET es_principal = NULL WHERE producto_id = :producto AND es_principal = 1');
        $quitarActual->execute(['producto' => $productoId]);
        $marcarNueva = $this->bd->prepare('UPDATE imagenes_producto SET es_principal = 1 WHERE id = :imagen AND producto_id = :producto');
        return $marcarNueva->execute(['imagen' => $imagenId, 'producto' => $productoId]);
    }

    /** Elimina una imagen y promueve otra si se eliminó la portada. */
    public function eliminarImagen(int $productoId, int $imagenId): string|false
    {
        $consulta = $this->bd->prepare('SELECT ruta_imagen, es_principal FROM imagenes_producto WHERE id = :imagen AND producto_id = :producto LIMIT 1');
        $consulta->execute(['imagen' => $imagenId, 'producto' => $productoId]);
        $imagen = $consulta->fetch();
        if (!$imagen) {
            return false;
        }

        $borrar = $this->bd->prepare('DELETE FROM imagenes_producto WHERE id = :imagen AND producto_id = :producto');
        $borrar->execute(['imagen' => $imagenId, 'producto' => $productoId]);

        if ((int) $imagen['es_principal'] === 1) {
            $promover = $this->bd->prepare('UPDATE imagenes_producto SET es_principal = 1 WHERE producto_id = :producto ORDER BY id ASC LIMIT 1');
            $promover->execute(['producto' => $productoId]);
        }

        return (string) $imagen['ruta_imagen'];
    }

    private function insertarImagen(int $productoId, string $ruta, ?int $esPrincipal): void
    {
        $consulta = $this->bd->prepare('INSERT INTO imagenes_producto (producto_id, ruta_imagen, es_principal) VALUES (:producto, :ruta, :principal)');
        $consulta->execute(['producto' => $productoId, 'ruta' => $ruta, 'principal' => $esPrincipal]);
    }

    public function resumenAdministrativo(): array
    {
        return $this->bd->query('SELECT COUNT(*) AS productos, COALESCE(SUM(stock), 0) AS unidades, COALESCE(SUM(activo = 1), 0) AS visibles FROM productos')->fetch();
    }
}
