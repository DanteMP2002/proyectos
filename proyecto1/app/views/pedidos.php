<?php
// 1. INICIALIZACIÓN Y SEGURIDAD
require_once __DIR__ . '/helpers/Autenticacion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

// Si no hay sesión iniciada, el usuario no tiene nada que hacer aquí. Lo mandamos al inicio.
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . URL_BASE . '/inicio');
    exit;
}

// 2. CONEXIÓN A LA BASE DE DATOS Y CONSULTA SEGURA
// Importamos tu clase de conexión (Ajusta la ruta según la ubicación de este archivo)

$usuario_id = $_SESSION['usuario']['id']; 
$pedidosCliente = [];

try {
    // Invocamos tu conexión estática mediantePDO
    $conexion = Conexion::obtener();
    
    // Ejecutamos la consulta filtrando por el ID del usuario logueado
    $stmt = $conexion->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC");
    $stmt->execute([$usuario_id]);
    $pedidosCliente = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Si necesitas depurar en desarrollo puedes descomentar la siguiente línea:
    // echo "Error: " . $e->getMessage();
    $pedidosCliente = [];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos | Vínculo Bodas</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <style>
        .seccion-mis-pedidos {
            width: min(1000px, calc(100% - 40px));
            margin: 40px auto;
            padding: 20px;
        }
        .cabecera-pedidos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ebd6ce;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .cabecera-pedidos h1 {
            font-family: Georgia, serif;
            color: var(--vino-oscuro);
            margin: 0;
        }
        .estado-pedido {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        /* Estados del cliente alineados a la paleta romántica */
        .estado-pendiente { background-color: #fef9e7; color: #f39c12; border: 1px solid #f9e79f; }
        .estado-pagado { background-color: #eafaf1; color: #2ecc71; border: 1px solid #d4efdf; }
        .estado-cancelado { background-color: #fceae9; color: #e74c3c; border: 1px solid #fadbd8; }
    </style>
</head>
<body>

    <main class="seccion-mis-pedidos">
        
        <div class="cabecera-pedidos">
            <div>
                <p class="etiqueta">Tu cuenta</p>
                <h1>Mis Pedidos</h1>
            </div>
            <a href="<?= URL_BASE ?>/inicio" class="boton-secundario">Volver a la tienda</a>
        </div>

        <p style="color: var(--suave);">Hola, <strong><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></strong>. Aquí puedes hacer el seguimiento de tus solicitudes para el gran día.</p>

        <!-- 3. RENDERIZADO DE LA TABLA -->
        <div class="tabla-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidosCliente)): ?>
                        <?php foreach ($pedidosCliente as $pedido): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($pedido['codigo'] ?? $pedido['id']) ?></strong></td>
                                <td><?= htmlspecialchars($pedido['creado_en'] ?? $pedido['fecha']) ?></td>
                                <td><small><?= htmlspecialchars(strtoupper($pedido['metodo_pago'])) ?></small></td>
                                <td><strong>S/ <?= number_format((float)$pedido['total'], 2) ?></strong></td>
                                <td>
                                    <?php 
                                    $estado = $pedido['estado'] ?? 'pendiente';
                                    if ($estado === 'pendiente'): ?>
                                        <span class="estado-pedido estado-pendiente">Pendiente de verificación</span>
                                    <?php elseif ($estado === 'pagado'): ?>
                                        <span class="estado-pedido estado-pagado">Pago Confirmado</span>
                                    <?php else: ?>
                                        <span class="estado-pedido estado-cancelado">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--suave);">
                                Aún no has registrado ningún pedido. ¡Explora nuestro catálogo para comenzar!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
