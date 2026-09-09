<?php 
$token = Autenticacion::tokenFormulario(); 
$pedido = $detalle['pedido']; 
$esAdmin = Autenticacion::esAdministrador();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido <?= htmlspecialchars($pedido['codigo']) ?> | MUNDO NOVIAS <span>& QUINCE</span></title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <?php if ($esAdmin): ?> <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css"> <?php endif; ?>
    
    
</head>
<body class="<?= $esAdmin ? 'body-admin cuerpo-detalle-pedido-admin' : 'cuerpo-detalle-pedido-cliente' ?>">

    <!-- Contenedor dinámico de clase estructural -->
    <main class="<?= $esAdmin ? 'tarjeta-blanca-seccion detalle-pedido-admin' : 'caja-detalle-cliente' ?>">
        
        <!-- Enlaces de Retorno Dinámicos -->
        <div class="detalle-cabecera-enlaces">
            <?php if ($esAdmin): ?>
                <a class="enlace-volver-admin" href="<?= URL_BASE ?>/pedido">← Volver a pedidos</a>
            <?php else: ?>
                <a class="enlace-regresar-detalle" href="<?= URL_BASE ?>/pedido/mispedidos">← Volver a mis pedidos</a>
            <?php endif; ?>
            
            <!-- Botón universal para regresar a ver el catálogo de la tienda -->
            <a href="<?= URL_BASE ?>/inicio" class="boton-secundario detalle-enlace-tienda">Ir a la Tienda</a>
        </div>
        
        <!-- Encabezado del Váucher -->
        <div class="detalle-encabezado <?= $esAdmin ? '' : 'linea-separadora' ?>">
            <span class="<?= $esAdmin ? 'subtitulo-seccion' : 'etiqueta' ?>">Resumen de Pedido: <?= htmlspecialchars($pedido['codigo']) ?></span>
            <h1 class="<?= $esAdmin ? 'detalle-titulo-admin' : 'texto-titulo' ?>"><?= htmlspecialchars($pedido['cliente']) ?></h1>
            <p class="detalle-meta-pedido"><?= htmlspecialchars($pedido['correo']) ?> · <?= htmlspecialchars($pedido['creado_en']) ?></p>
        </div>

        <!-- Tabla Detallada de Artículos -->
        <div class="tabla-responsive">
            <table class="<?= $esAdmin ? 'tabla-admin' : '' ?>">
                <thead>
                    <tr>
                        <th class="<?= !$esAdmin ? 'detalle-th-cliente' : '' ?>">Producto</th>
                        <th class="<?= !$esAdmin ? 'detalle-th-cliente' : '' ?>">Cantidad</th>
                        <th class="<?= !$esAdmin ? 'detalle-th-cliente' : '' ?>">Precio</th>
                        <th class="<?= !$esAdmin ? 'detalle-th-cliente' : '' ?>">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle['productos'] as $producto): ?>
                        <tr>
                            <td class="detalle-producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= (int)$producto['cantidad'] ?> unds</td>
                            <td class="detalle-precio"> <?= !$esAdmin ? 'S/' : '' ?> <?= number_format((float)$producto['precio_unitario'], 2) ?></td>
                            <td class="<?= $esAdmin ? 'col-precio' : 'texto-resaltado' ?> detalle-precio detalle-subtotal"><?= !$esAdmin ? 'S/' : '' ?> <?= number_format((float)$producto['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bloque de Totales -->
        <div class="detalle-total <?= $esAdmin ? '' : 'linea-separadora' ?>">
            <span>TOTAL FACTURADO:</span>
            <strong class="<?= $esAdmin ? 'detalle-total-admin' : 'detalle-total-cliente' ?>">S/ <?= number_format((float)$pedido['total'], 2) ?></strong>
        </div>

        <!-- RENDERIZADO CONDICIONAL: Interfaz de control exclusiva para el Administrador -->
        <?php if ($esAdmin): ?>
            <form action="<?= URL_BASE ?>/pedido/estado/<?= (int)$pedido['id'] ?>" method="post" class="formulario-estado-admin">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label>
                    Estado del pedido
                    <select name="estado" required>
                        <option value="pendiente" <?= $pedido['estado'] === 'pendiente' ? 'selected' : '' ?>>⏳ Pendiente</option>
                        <option value="pagado" <?= $pedido['estado'] === 'pagado' ? 'selected' : '' ?>>✅ Pagado</option>
                        <option value="cancelado" <?= $pedido['estado'] === 'cancelado' ? 'selected' : '' ?>>❌ Cancelado</option>
                    </select>
                </label>
                <button class="boton-admin-principal boton-actualizar-estado">Actualizar estado</button>
            </form>
        <?php else: ?>
            <!-- Bloque informativo estático exclusivo para el Cliente -->
            <div class="detalle-estado-cliente">
                <span>Estado de verificación:</span>
                <span class="badge-estado-cliente estado-<?= htmlspecialchars($pedido['estado']) ?>">
                    <?= htmlspecialchars($pedido['estado'] === 'pendiente' ? 'Pendiente de aprobación' : ($pedido['estado'] === 'pagado' ? 'Pago verificado' : 'Cancelado')) ?>
                </span>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
