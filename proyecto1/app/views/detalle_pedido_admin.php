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
    <title>Pedido <?= htmlspecialchars($pedido['codigo']) ?></title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <?php if ($esAdmin): ?>
        <!-- Solo carga el estilo oscuro premium si es el administrador -->
        <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
    <?php endif; ?>
    
    <!-- Estilos adaptativos locales para el cliente si no es administrador -->
    <?php if (!$esAdmin): ?>
    <style>
        .caja-detalle-cliente {
            background-color: var(--blanco);
            border: 1px solid #efd9d1;
            box-shadow: 0 10px 25px rgba(122, 40, 71, 0.05);
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 700px;
        }
        .linea-separadora { border-bottom: 1px solid #ebd6ce; }
        .texto-titulo { color: var(--vino-oscuro); font-family: Georgia, serif; }
        .texto-resaltado { color: var(--vino); }
        .badge-estado-cliente {
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }
        .estado-pendiente { background-color: #fef9e7; color: #f39c12; border: 1px solid #f9e79f; }
        .estado-pagado { background-color: #eafaf1; color: #2ecc71; border: 1px solid #d4efdf; }
        .estado-cancelado { background-color: #fceae9; color: #e74c3c; border: 1px solid #fadbd8; }
    </style>
    <?php endif; ?>
</head>
<body class="<?= $esAdmin ? 'body-admin' : '' ?>" style="display: grid; min-height: 100vh; place-items: center; padding: 20px; background-color: <?= $esAdmin ? 'var(--bg-dark)' : 'var(--crema)' ?>;">

    <!-- Contenedor dinámico de clase estructural -->
    <main class="<?= $esAdmin ? 'tarjeta-blanca-seccion' : 'caja-detalle-cliente' ?>" style="<?= $esAdmin ? 'max-width: 700px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.5);' : '' ?>">
        
        <!-- Enlaces de Retorno Dinámicos -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <?php if ($esAdmin): ?>
                <a href="<?= URL_BASE ?>/pedido" style="color: var(--oro-rosa); font-weight: bold;">← Volver a pedidos</a>
            <?php else: ?>
                <a href="<?= URL_BASE ?>/pedido/mispedidos" style="color: var(--suave); font-weight: bold;">← Volver a mis pedidos</a>
            <?php endif; ?>
            
            <!-- Botón universal para regresar a ver el catálogo de la tienda -->
            <a href="<?= URL_BASE ?>/inicio" class="boton-secundario" style="padding: 6px 12px; font-size: 0.85rem;">Ir a la Tienda</a>
        </div>
        
        <!-- Encabezado del Váucher -->
        <div style="padding-bottom: 15px; margin-bottom: 20px;" class="<?= $esAdmin ? '' : 'linea-separadora' ?>">
            <span class="<?= $esAdmin ? 'subtitulo-seccion' : 'etiqueta' ?>">Resumen de Pedido: <?= htmlspecialchars($pedido['codigo']) ?></span>
            <h1 class="<?= $esAdmin ? '' : 'texto-titulo' ?>" style="<?= $esAdmin ? 'color: var(--texto-blanco); font-family: Georgia, serif; margin: 5px 0 0 0;' : 'margin: 5px 0 0 0; font-size: 1.8rem;' ?>"><?= htmlspecialchars($pedido['cliente']) ?></h1>
            <p style="color: var(--suave); margin: 5px 0 0 0; font-size: 0.9rem;"><?= htmlspecialchars($pedido['correo']) ?> · <?= htmlspecialchars($pedido['creado_en']) ?></p>
        </div>

        <!-- Tabla Detallada de Artículos -->
        <div class="tabla-responsive">
            <table class="<?= $esAdmin ? 'tabla-admin' : '' ?>">
                <thead>
                    <tr>
                        <th style="<?= !$esAdmin ? 'color: var(--vino-oscuro);' : '' ?>">Producto</th>
                        <th style="<?= !$esAdmin ? 'color: var(--vino-oscuro);' : '' ?>">Cantidad</th>
                        <th style="<?= !$esAdmin ? 'color: var(--vino-oscuro);' : '' ?>">Precio</th>
                        <th style="<?= !$esAdmin ? 'color: var(--vino-oscuro);' : '' ?>">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle['productos'] as $producto): ?>
                        <tr>
                            <td style="font-weight: bold;"><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= (int)$producto['cantidad'] ?> unds</td>
                            <td style="font-family: monospace;"><?= !$esAdmin ? 'S/' : '' ?> <?= number_format((float)$producto['precio_unitario'], 2) ?></td>
                            <td style="font-family: monospace; font-weight: bold;" class="<?= $esAdmin ? 'col-precio' : 'texto-resaltado' ?>"><?= !$esAdmin ? 'S/' : '' ?> <?= number_format((float)$producto['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bloque de Totales -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 25px 0; padding-top: 15px;" class="<?= $esAdmin ? '' : 'linea-separadora' ?>">
            <span style="font-weight: bold; color: var(--suave);">TOTAL FACTURADO:</span>
            <strong style="font-size: 1.6rem; color: <?= $esAdmin ? 'var(--oro-metalico)' : 'var(--vino)' ?>; font-family: Georgia, serif;">S/ <?= number_format((float)$pedido['total'], 2) ?></strong>
        </div>

        <!-- RENDERIZADO CONDICIONAL: Interfaz de control exclusiva para el Administrador -->
        <?php if ($esAdmin): ?>
            <form action="<?= URL_BASE ?>/pedido/estado/<?= (int)$pedido['id'] ?>" method="post" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 8px; border: 1px solid var(--lineas); display: grid; gap: 15px;">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 8px;">
                    Estado del pedido
                    <select name="estado" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas); padding: 10px; border-radius: 4px; outline: none;">
                        <option value="pendiente" <?= $pedido['estado'] === 'pendiente' ? 'selected' : '' ?>>⏳ Pendiente</option>
                        <option value="pagado" <?= $pedido['estado'] === 'pagado' ? 'selected' : '' ?>>✅ Pagado</option>
                        <option value="cancelado" <?= $pedido['estado'] === 'cancelado' ? 'selected' : '' ?>>❌ Cancelado</option>
                    </select>
                </label>
                <button class="boton-admin-principal" style="border: none; cursor: pointer; padding: 12px;">Actualizar estado</button>
            </form>
        <?php else: ?>
            <!-- Bloque informativo estático exclusivo para el Cliente -->
            <div style="text-align: center; margin-top: 20px; padding: 15px; background: #fffdfc; border-radius: 6px; border: 1px solid #ebd6ce;">
                <span style="font-weight: bold; color: var(--suave); margin-right: 10px; font-size: 0.95rem;">Estado de verificación:</span>
                <span class="badge-estado-cliente estado-<?= htmlspecialchars($pedido['estado']) ?>">
                    <?= htmlspecialchars($pedido['estado'] === 'pendiente' ? 'Pendiente de aprobación' : ($pedido['estado'] === 'pagado' ? 'Pago verificado' : 'Cancelado')) ?>
                </span>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
