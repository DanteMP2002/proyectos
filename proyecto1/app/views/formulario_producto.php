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
                    <input name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" maxlength="150" required>
                </label>

                <label>Categoría
                    <select name="categoria" required style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="" disabled>Selecciona una categoría...</option>
                        
                        <?php 
                        // Recuperamos la categoría actual del producto para marcarla como seleccionada
                        $categoriaActual = htmlspecialchars($producto['categoria']); 
                        ?>

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

                <label>Descripción
                    <textarea name="descripcion" rows="4" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                </label>

                <div class="campos-dobles">
                    <label>Precio (S/)
                        <input name="precio" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string)$producto['precio']) ?>" required>
                    </label>
                    <label>Stock disponible
                        <input name="stock" type="number" min="0" step="1" value="<?= (int)$producto['stock'] ?>" required>
                    </label>
                </div>

                <label>Imagen del producto 
                    <input name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
                </label>

                <small>JPG, PNG o WEBP. Máximo 5 MB. Si no seleccionas una imagen al editar, se conserva la actual.</small>

                <?php if ($producto['imagen']): ?>
                    <img class="vista-imagen-admin" src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen actual">
                <?php endif; ?>

                <label class="interruptor">
                    <input name="activo" type="checkbox" <?= $producto['activo'] ? 'checked' : '' ?>>
                    <span>Mostrar este producto en la tienda</span>
                </label>

                <button class="boton-principal ancho-completo">Guardar producto</button>

            </form>
        </main>
    </body>
</html>
