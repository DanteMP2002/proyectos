<?php
require_once __DIR__ . '/../../config/conexion.php';

// Registra compras y descuenta el stock de forma atómica.
class Pedido {
    private PDO $bd;

    public function __construct() { $this->bd = Conexion::obtener(); }

    public function crear(int $usuarioId, array $carrito, string $metodoPago): int {
        $this->bd->beginTransaction();
        try {
            $total = 0;
            $lineas = [];
            $buscar = $this->bd->prepare('SELECT id, nombre, precio, stock, activo FROM productos WHERE id = :id FOR UPDATE');
            // Se vuelve a comprobar precio y stock: el carrito del navegador no es confiable.
            foreach ($carrito as $item) {
                $buscar->execute(['id' => (int)$item['id']]);
                $producto = $buscar->fetch();
                if (!$producto || !$producto['activo'] || $producto['stock'] < $item['cantidad']) throw new RuntimeException('Uno de los productos ya no tiene stock suficiente.');
                $subtotal = (float)$producto['precio'] * (int)$item['cantidad'];
                $total += $subtotal;
                $lineas[] = ['producto' => $producto, 'cantidad' => (int)$item['cantidad'], 'subtotal' => $subtotal];
            }
            $codigo = 'BOD-' . date('YmdHis') . '-' . random_int(100, 999);
            $crearPedido = $this->bd->prepare("INSERT INTO pedidos (codigo, usuario_id, total, metodo_pago, estado) VALUES (:codigo, :usuario, :total, :metodo, 'pendiente')");
            $crearPedido->execute(['codigo' => $codigo, 'usuario' => $usuarioId, 'total' => $total, 'metodo' => $metodoPago]);
            $pedidoId = (int)$this->bd->lastInsertId();
            $crearDetalle = $this->bd->prepare('INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (:pedido, :producto, :cantidad, :precio, :subtotal)');
            $descontar = $this->bd->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id = :id');
            foreach ($lineas as $linea) {
                $crearDetalle->execute(['pedido' => $pedidoId, 'producto' => $linea['producto']['id'], 'cantidad' => $linea['cantidad'], 'precio' => $linea['producto']['precio'], 'subtotal' => $linea['subtotal']]);
                $descontar->execute(['cantidad' => $linea['cantidad'], 'id' => $linea['producto']['id']]);
            }
            $this->bd->commit();
            return $pedidoId;
        } catch (Throwable $error) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            throw $error;
        }
    }

    // Datos que ve el administrador sin exponer contraseñas de los clientes.
    public function listarAdministracion(): array {
        return $this->bd->query('SELECT p.*, u.nombre AS cliente, u.correo FROM pedidos p INNER JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.creado_en DESC')->fetchAll();
    }

    public function detalleAdministracion(int $id): array {
        $pedido = $this->bd->prepare('SELECT p.*, u.nombre AS cliente, u.correo FROM pedidos p INNER JOIN usuarios u ON u.id = p.usuario_id WHERE p.id = :id');
        $pedido->execute(['id' => $id]);
        $cabecera = $pedido->fetch();
        if (!$cabecera) return [];
        $detalle = $this->bd->prepare('SELECT d.*, pr.nombre FROM detalle_pedidos d INNER JOIN productos pr ON pr.id = d.producto_id WHERE d.pedido_id = :id');
        $detalle->execute(['id' => $id]);
        return ['pedido' => $cabecera, 'productos' => $detalle->fetchAll()];
    }

    public function resumenAdministrativo(): array {
        return $this->bd->query("SELECT COUNT(*) AS pedidos, COALESCE(SUM(estado = 'pendiente'), 0) AS pendientes, COALESCE(SUM(CASE WHEN estado <> 'cancelado' THEN total ELSE 0 END), 0) AS ventas FROM pedidos")->fetch();
    }

    // Cancelar devuelve unidades al stock; reactivar las vuelve a descontar tras comprobar disponibilidad.
    public function actualizarEstado(int $id, string $nuevoEstado): bool {
        if (!in_array($nuevoEstado, ['pendiente', 'pagado', 'cancelado'], true)) return false;
        $this->bd->beginTransaction();
        try {
            $buscar = $this->bd->prepare('SELECT estado FROM pedidos WHERE id = :id FOR UPDATE');
            $buscar->execute(['id' => $id]);
            $actual = $buscar->fetch();
            if (!$actual) throw new RuntimeException('Pedido no encontrado.');
            if ($actual['estado'] === $nuevoEstado) { $this->bd->commit(); return true; }
            $detalle = $this->bd->prepare('SELECT producto_id, cantidad FROM detalle_pedidos WHERE pedido_id = :id');
            $detalle->execute(['id' => $id]);
            $lineas = $detalle->fetchAll();
            if ($nuevoEstado === 'cancelado') {
                $sumar = $this->bd->prepare('UPDATE productos SET stock = stock + :cantidad WHERE id = :id');
                foreach ($lineas as $linea) $sumar->execute(['cantidad' => $linea['cantidad'], 'id' => $linea['producto_id']]);
            }
            if ($actual['estado'] === 'cancelado') {
                $restar = $this->bd->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad');
                foreach ($lineas as $linea) {
                    $restar->execute(['cantidad' => $linea['cantidad'], 'id' => $linea['producto_id']]);
                    if ($restar->rowCount() !== 1) throw new RuntimeException('No hay stock para reactivar este pedido.');
                }
            }
            $actualizar = $this->bd->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
            $actualizar->execute(['estado' => $nuevoEstado, 'id' => $id]);
            $this->bd->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->bd->inTransaction()) $this->bd->rollBack();
            throw $error;
        }
    }
}
