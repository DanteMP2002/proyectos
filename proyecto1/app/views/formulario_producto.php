<?php $token = Autenticacion::tokenFormulario(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Vínculo Bodas</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
</head>
<body class="body-admin" style="display: grid; min-height: 100vh; place-items: center; padding: 40px 20px;">

    <main class="tarjeta-blanca-seccion" style="max-width: 600px; width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
        <a href="<?= URL_BASE ?>/admin" style="color: var(--oro-rosa); font-weight: bold; text-decoration: none; display: inline-block; margin-bottom: 15px;">← Volver al panel</a>
        
        <span class="subtitulo-seccion">Catálogo</span>
        <h1 style="color: var(--texto-blanco); font-family: Georgia, serif; margin: 5px 0 25px 0; border-bottom: 1px solid var(--lineas); padding-bottom: 10px;"><?= htmlspecialchars($titulo) ?></h1>

        <form action="<?= $accion ?>" method="post" enctype="multipart/form-data" style="display: grid; gap: 20px;">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Nombre del producto
                <input name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" maxlength="150" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas);">
            </label>

            <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Categoría
                <select name="categoria" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas); padding: 11px; border-radius: 4px;">
                    <option value="" disabled>Selecciona una categoría...</option>
                    <?php $categoriaActual = htmlspecialchars($producto['categoria']); ?>
                    <option value="Vestidos" <?= $categoriaActual == 'Vestidos' ? 'selected' : '' ?>>Vestidos de novia</option>
                    <option value="Trajes" <?= $categoriaActual == 'Trajes' ? 'selected' : '' ?>>Trajes de novio</option>
                    <option value="Accesorios" <?= $categoriaActual == 'Accesorios' ? 'selected' : '' ?>>Accesorios y Joyería</option>
                    <option value="Decoraciones" <?= $categoriaActual == 'Decoraciones' ? 'selected' : '' ?>>Decoración y Arreglos</option>
                    <option value="Bebidas" <?= $categoriaActual == 'Bebidas' ? 'selected' : '' ?>>Bebidas y Coctelería</option>
                    <option value="Bocaditos" <?= $categoriaActual == 'Bocaditos' ? 'selected' : '' ?>>Bocaditos y Catering</option>
                    <option value="Pasteles y Postres" <?= $categoriaActual == 'Pasteles y Postres' ? 'selected' : '' ?>>Pasteles y Postres</option>
                    <option value="Tarjetas" <?= $categoriaActual == 'Tarjetas' ? 'selected' : '' ?>>Tarjetas e Invitaciones</option>
                    <option value="Misceláneos" <?= $categoriaActual == 'Misceláneos' ? 'selected' : '' ?>>Otros / Misceláneos</option>
                </select>
            </label>

            <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Descripción
                <textarea name="descripcion" rows="4" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas); padding: 11px; border-radius: 4px; font-family: inherit; width: 100%; resize: vertical;"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Precio (S/)
                    <input name="precio" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string)$producto['precio']) ?>" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas);">
                </label>
                <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Stock disponible
                    <input name="stock" type="number" min="0" step="1" value="<?= (int)$producto['stock'] ?>" required style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas);">
                </label>
            </div>

            <label style="color: var(--oro-rosa); font-weight: bold; display: grid; gap: 6px;">Imagen del producto 
                <input name="imagen" type="file" accept="image/jpeg,image/png,image/webp" style="background: var(--bg-dark); color: var(--texto-blanco); border: 1px solid var(--lineas);">
                <small style="color: var(--texto-gris); font-weight: normal; margin-top: 4px;">JPG, PNG o WEBP. Máximo 5 MB.</small>
            </label>

            <?php if ($producto['imagen']): ?>
                <div style="text-align: center; border: 1px solid var(--lineas); padding: 10px; border-radius: 6px; background: rgba(0,0,0,0.2);">
                    <p style="margin: 0 0 10px 0; font-size: 0.8rem; color: var(--texto-gris);">Imagen actual:</p>
                    <img src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen actual" style="max-height: 150px; border-radius: 4px; object-fit: cover;">
                </div>
            <?php endif; ?>

            <label style="display: flex; align-items: center; gap: 10px; color: var(--texto-blanco); font-weight: bold; cursor: pointer; margin: 10px 0;">
                <input name="activo" type="checkbox" <?= $producto['activo'] ? 'checked' : '' ?> style="width: auto; cursor: pointer;">
                <span>Mostrar este producto en la tienda</span>
            </label>

            <button class="boton-admin-principal" style="border: none; cursor: pointer; padding: 14px; font-size: 1rem;">Guardar producto</button>
        </form>
    </main>

</body>
</html>
