<?php
$token = Autenticacion::tokenFormulario();
$esCreacion = empty($producto['id']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Vínculo Bodas</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
</head>
<body class="body-admin pagina-admin-centro">
    <main class="tarjeta-blanca-seccion formulario-admin-contenedor">
        <a href="<?= URL_BASE ?>/admin" class="enlace-volver-admin">← Volver al panel</a>
        <header class="cabecera-formulario-admin">
            <p class="subtitulo-seccion">Catálogo</p>
            <h1><?= htmlspecialchars($titulo) ?></h1>
            <p>Completa los datos visibles para los clientes de la tienda.</p>
        </header>

        <!-- El token protege el envío y el controlador valida los datos recibidos. -->
        <form action="<?= $accion ?>" method="post" enctype="multipart/form-data" class="formulario-admin">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label>Nombre del producto<input name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" maxlength="150" required></label>
            <label>Categoría
                <?php $categoriaActual = $producto['categoria']; ?>
                <select name="categoria" required>
                    <option value="" disabled <?= $categoriaActual === '' ? 'selected' : '' ?>>Selecciona una categoría</option>
                    <option value="Vestidos" <?= $categoriaActual === 'Vestidos' ? 'selected' : '' ?>>Vestidos de novia</option>
                    <option value="Trajes" <?= $categoriaActual === 'Trajes' ? 'selected' : '' ?>>Trajes de novio</option>
                    <option value="Accesorios" <?= $categoriaActual === 'Accesorios' ? 'selected' : '' ?>>Accesorios y joyería</option>
                    <option value="Decoraciones" <?= $categoriaActual === 'Decoraciones' ? 'selected' : '' ?>>Decoración y arreglos</option>
                    <option value="Bebidas" <?= $categoriaActual === 'Bebidas' ? 'selected' : '' ?>>Bebidas y coctelería</option>
                    <option value="Bocaditos" <?= $categoriaActual === 'Bocaditos' ? 'selected' : '' ?>>Bocaditos y catering</option>
                    <option value="Pasteles y Postres" <?= $categoriaActual === 'Pasteles y Postres' ? 'selected' : '' ?>>Pasteles y postres</option>
                    <option value="Tarjetas" <?= $categoriaActual === 'Tarjetas' ? 'selected' : '' ?>>Tarjetas e invitaciones</option>
                    <option value="Misceláneos" <?= $categoriaActual === 'Misceláneos' ? 'selected' : '' ?>>Otros</option>
                </select>
            </label>
            <label>Descripción<textarea name="descripcion" rows="4" required><?= htmlspecialchars($producto['descripcion']) ?></textarea></label>
            <div class="fila-campos-admin"><label>Precio (S/)<input name="precio" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string) $producto['precio']) ?>" required></label><label>Stock disponible<input name="stock" type="number" min="0" step="1" value="<?= (int) $producto['stock'] ?>" required></label></div>
            <!-- La portada se marca con es_principal = 1; las demás fotos son opcionales. -->
            <fieldset class="grupo-imagenes-admin">
                <legend>Imágenes del producto</legend>
                <label>Imagen principal (portada)
                    <?php if ($esCreacion): ?>
                        <input name="imagen_principal" type="file" accept="image/jpeg,image/png,image/webp" required>
                        <small>Obligatoria para crear el producto.</small>
                    <?php else: ?>
                        <small>Para editar una foto existente, selecciónala en la galería inferior y elige un archivo de reemplazo.</small>
                    <?php endif; ?>
                </label>
                <label>Imágenes adicionales
                    <input name="imagenes_adicionales[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                    <small>Opcional. Puedes seleccionar varias fotos con Ctrl o Shift.</small>
                </label>
            </fieldset>

            <?php
            $etiquetaImagen = static function (int $indice): string {
                $etiqueta = '';
                do {
                    $etiqueta = chr(65 + ($indice % 26)) . $etiqueta;
                    $indice = intdiv($indice, 26) - 1;
                } while ($indice >= 0);
                return $etiqueta;
            };
            ?>
            <?php if ($imagenes): ?>
                <section class="galeria-imagenes-admin" aria-labelledby="titulo-imagenes-actuales">
                    <h2 id="titulo-imagenes-actuales">Imágenes actuales</h2>
                    <div>
                        <?php foreach ($imagenes as $indiceImagen => $imagen): ?>
                            <?php $letraImagen = $etiquetaImagen($indiceImagen); ?>
                            <figure class="vista-previa-imagen">
                                <img src="<?= URL_BASE ?>/<?= htmlspecialchars($imagen['ruta_imagen']) ?>" alt="Imagen del producto">
                                <figcaption><strong><?= $letraImagen ?></strong> · <?= (int) $imagen['es_principal'] === 1 ? 'Portada actual' : 'Imagen adicional' ?></figcaption>
                                <label class="selector-imagen-existente">
                                    <input type="radio" name="imagen_id_reemplazar" value="<?= (int) $imagen['id'] ?>">
                                    Seleccionar para reemplazar
                                </label>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                    <label class="reemplazo-imagen-admin">Archivo de reemplazo
                        <input name="imagen_reemplazo" type="file" accept="image/jpeg,image/png,image/webp">
                        <small>Solo se reemplaza la imagen seleccionada. Las demás se conservan.</small>
                    </label>
                </section>
            <?php endif; ?>
            <label class="campo-checkbox"><input name="activo" type="checkbox" <?= $producto['activo'] ? 'checked' : '' ?>><span>Mostrar este producto en la tienda</span></label>
            <button class="boton-admin-principal" type="submit">Guardar producto</button>
        </form>
    </main>
</body>
</html>
