<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
$mensajeCompra = $_SESSION['mensaje_compra'] ?? '';
unset($_SESSION['mensaje_compra']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vínculo Bodas | Tienda para tu celebración</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
</head>
<body>
    <!-- Encabezado público: cualquiera puede recorrer el catálogo. -->
    <header class="encabezado-principal">
        <a class="marca" href="<?= URL_BASE ?>/inicio">Vínculo <span>Bodas</span></a>
        <nav class="navegacion" aria-label="Navegación principal"><a href="#productos">Productos</a><a href="#nosotros">Nosotros</a><?php if (Autenticacion::esAdministrador()): ?><a href="<?= URL_BASE ?>/admin">Panel</a><?php endif; ?></nav>
        <!-- Este botón despliega el mini carrito sin salir del catálogo. -->
        <button class="boton-carrito" id="boton-carrito" type="button" aria-expanded="false">Carrito <span id="contador-carrito">0</span></button>
    </header>

    <!-- Presentación visual de la tienda. -->
    <section class="portada"><p class="etiqueta">Detalles para recordar</p><h1>Todo para celebrar <em>su gran historia.</em></h1><p>Encuentra piezas elegidas para bodas íntimas, celebraciones inolvidables y regalos con significado.</p><a class="boton-principal" href="#productos">Explorar colección</a></section>

    <main>
        <?php if ($mensajeCompra): ?><p class="aviso-compra"><?= htmlspecialchars($mensajeCompra) ?></p><?php endif; ?>
        <!-- Catálogo obtenido de la tabla productos. -->
        <section class="seccion-productos" id="productos"><div class="titulo-seccion"><p class="etiqueta">Nuestra selección</p><h2>Productos destacados</h2></div><div class="rejilla-productos">
            <?php foreach ($productos as $producto): ?>
                <article class="tarjeta-producto"><img src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen'] ?: 'public/img/logo.jpg') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>"><div class="contenido-producto"><span class="categoria-producto"><?= htmlspecialchars($producto['categoria']) ?></span><h3><?= htmlspecialchars($producto['nombre']) ?></h3><p class="descripcion-producto"><?= htmlspecialchars($producto['descripcion']) ?></p><div class="pie-producto"><strong>S/ <?= number_format((float)$producto['precio'], 2) ?></strong><button class="boton-secundario" data-agregar="<?= (int)$producto['id'] ?>">Añadir</button></div></div></article>
            <?php endforeach; ?>
        </div></section>
        <!-- Contenido informativo de la tienda. -->
        <section class="seccion-nosotros" id="nosotros"><p class="etiqueta">Con cariño</p><h2>Cada detalle cuenta una historia</h2><p>Compra como invitado con tranquilidad. Solo pediremos tu cuenta cuando estés listo para confirmar tu pedido.</p></section>
    </main>

    <!-- Panel lateral que muestra, modifica y elimina productos del carrito. -->
    <aside class="panel-carrito" id="panel-carrito" aria-hidden="true"><div class="cabecera-panel"><h2>Tu carrito</h2><button class="boton-cerrar" data-cerrar="panel-carrito" aria-label="Cerrar carrito">×</button></div><div id="contenido-carrito" class="contenido-carrito"><p>Tu carrito está vacío.</p></div><div class="pie-carrito"><p>Total <strong id="total-carrito">S/ 0.00</strong></p><button class="boton-principal ancho-completo" id="boton-comprar">Continuar con la compra</button></div></aside>
    <div class="fondo-modal" id="fondo-modal"></div>

    <!-- Modal mostrado al invitado al momento de comprar, no antes. -->
    <section class="modal-acceso" id="modal-acceso" aria-hidden="true" role="dialog" aria-labelledby="titulo-acceso"><button class="boton-cerrar" data-cerrar="modal-acceso" aria-label="Cerrar acceso">×</button><div id="vista-ingreso"><p class="etiqueta">Casi listo</p><h2 id="titulo-acceso">Ingresa para finalizar tu compra</h2><p>Tu carrito se mantendrá guardado.</p><form id="formulario-ingreso"><label>Correo<input name="correo" type="email" required></label><label>Contraseña<input name="clave" type="password" required></label><button class="boton-principal ancho-completo">Iniciar sesión</button></form><p class="cambio-formulario">¿No tienes una cuenta? <button data-mostrar-registro type="button">Regístrate</button></p></div><div id="vista-registro" hidden><p class="etiqueta">Tu cuenta</p><h2>Regístrate para continuar</h2><form id="formulario-registro"><label>Nombre<input name="nombre" required></label><label>Correo<input name="correo" type="email" required></label><label>Contraseña<input name="clave" type="password" minlength="6" required></label><button class="boton-principal ancho-completo">Crear cuenta</button></form><p class="cambio-formulario">¿Ya tienes cuenta? <button data-mostrar-ingreso type="button">Inicia sesión</button></p></div><p class="mensaje-formulario" id="mensaje-acceso"></p></section>

    <!-- Solo Ctrl + Shift + A abre este modal. La seguridad la hace el servidor al validar los tres datos. -->
    <section class="modal-acceso" id="modal-administrador" aria-hidden="true" role="dialog" aria-labelledby="titulo-administrador"><button class="boton-cerrar" data-cerrar="modal-administrador" aria-label="Cerrar acceso de administrador">×</button><p class="etiqueta">Acceso restringido</p><h2 id="titulo-administrador">Administración</h2><form id="formulario-administrador"><label>Correo administrador<input name="correo" type="email" required></label><label>Contraseña<input name="clave" type="password" required></label><label>Código adicional<input name="codigo_admin" type="password" required></label><button class="boton-principal ancho-completo">Validar acceso</button></form><p class="mensaje-formulario" id="mensaje-administrador"></p></section>

    <footer class="pie-pagina">© <?= date('Y') ?> Vínculo Bodas · Diseñado para celebrar.</footer>
    <script>window.URL_BASE = '<?= URL_BASE ?>';</script><script src="<?= URL_BASE ?>/public/js/tienda.js"></script>
</body></html>
