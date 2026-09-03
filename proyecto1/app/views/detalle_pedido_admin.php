<?php $token = Autenticacion::tokenFormulario(); $pedido = $detalle['pedido']; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido <?= htmlspecialchars($pedido['codigo']) ?></title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
</head>
<body class="body-admin" style="display: grid; min-height: 100vh; place-items: center; padding: 20px;">

    <main class="tarjeta-blanca-seccion" style="max-width: 700px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
        <a href="<?= URL_BASE ?>/pedido" style="color: var(--oro-rosa); font-weight: bold; text-decoration: none; display: inline-block; margin-bottom: 15px;">← Volver a pedidos</a>
        
        <div style="border-bottom: 1px solid var(--lineas); padding-bottom: 15px; margin-bottom: 20px;">
            <span class="subtitulo-seccion">Pedido: <?= htmlspecialchars($pedido['codigo']) ?></span>
            <h1 style="color: var(--texto-blanco); font-family: Georgia, serif; margin: 5px 0 0 0;"><?= htmlspecialchars($pedido['cliente']) ?></h1>
            <p style="color: var(--texto-gris); margin: 5px 0 0 0; font-size: 0.9rem;"><?= htmlspecialchars($pedido['correo']) ?> · <?= htmlspecialchars($pedido['creado_en']) ?></p>
        </div>

        <div class="contenedor-tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle['productos'] as $producto): ?>
                        <tr>
                            <td class="col-nombre-producto"><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= (int)$producto['cantidad'] ?> unds</td>
                            <td class="col-precio">S/ <?= number_format((float)$producto['precio_unitario'], 2) ?></td>
                            <td class="col-precio">S/ <?= number_format((float)$producto['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 25px 0; padding-top: 15px; border-top: 1px solid var(--lineas);">
            <span style="font-weight: bold; color: var(--texto-gris);">TOTAL DEL PEDIDO:</span>
            <strong style="font-size: 1.6rem; color: var(--oro-metalico);">S/ <?= number_format((float)$pedido['total'], 2) ?></strong>
        </div>

        <!-- Formulario de Cambio de Estado -->
        <form action="<?= URL_BASE ?>/pedido/estado/<?= (int)$pedido['id'] ?>" method="post" style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 8px; border: 1px solid var(--lineas); display: grid; gap: 15px;">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 8px;">
                Estado del pedido
                <select name="estado" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas); padding: 10px; border-radius: 4px; outline: none;">
                    <option value="pendiente" <?= $pedido['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="pagado" <?= $pedido['estado'] === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                    <option value="cancelado" <?= $pedido['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </label>
            <button class="boton-admin-principal" style="border: none; cursor: pointer; padding: 12px;">Actualizar estado</button>
        </form>
    </main>

</body>
</html>
