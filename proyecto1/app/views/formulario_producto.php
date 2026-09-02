<?php $token = Autenticacion::tokenFormulario(); ?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($titulo) ?> | Vínculo Bodas</title>
        <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    </head>
    <body class="pagina-simple">
        <main class="caja-checkout">
            <a href="<?= URL_BASE ?>/admin">← Volver al panel</a>
            <p class="etiqueta">Catálogo</p>
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <!-- enctype permite enviar el archivo de imagen junto con los demás datos. -->
            <form action="<?= $accion ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label>Nombre del producto
                <input name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" maxlength="150" required></label><label>Categoría
                    <input name="categoria" value="<?= htmlspecialchars($producto['categoria']) ?>" maxlength="80" placeholder="Vestidos, decoración, joyería..." required></label><label>Descripción<textarea name="descripcion" rows="4" required><?= htmlspecialchars($producto['descripcion']) ?></textarea></label><div class="campos-dobles"><label>Precio (S/)<input name="precio" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string)$producto['precio']) ?>" required></label><label>Stock disponible<input name="stock" type="number" min="0" step="1" value="<?= (int)$producto['stock'] ?>" required></label></div><label>Imagen del producto <input name="imagen" type="file" accept="image/jpeg,image/png,image/webp"></label><small>JPG, PNG o WEBP. Máximo 5 MB. Si no seleccionas una imagen al editar, se conserva la actual.</small><?php if ($producto['imagen']): ?><img class="vista-imagen-admin" src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen actual"><?php endif; ?>
                <label class="interruptor">
                    <input name="activo" type="checkbox" <?= $producto['activo'] ? 'checked' : '' ?>><span>Mostrar este producto en la tienda</span>
                </label>
                <button class="boton-principal ancho-completo">
                <?php echo $accion ?>    
                Guardar producto</button>
            </form>
        </main>
    </body>
</html>
