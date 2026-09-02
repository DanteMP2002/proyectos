<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';

$mensajeCompra = $_SESSION['mensaje_compra'] ?? '';
unset($_SESSION['mensaje_compra']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vínculo Bodas - Tienda</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
</head>
<body>

    <!-- ─── ENCABEZADO ─────────────────────────────────────────────────────── -->
    <!-- Cualquiera puede recorrer el catálogo sin iniciar sesión.              -->
    <header class="encabezado-principal">
        <a class="marca" href="<?= URL_BASE ?>/inicio">
            Vínculo <span>Bodas</span>
        </a>

        <nav class="navegacion" aria-label="Navegación principal">
            <a href="#productos">Productos</a>
            <a href="#nosotros">Nosotros</a>
            <?php if (Autenticacion::esAdministrador()): ?>
                <a href="<?= URL_BASE ?>/admin">Panel</a>
            <?php endif; ?>
        </nav>

        <!-- Despliega el mini carrito sin salir del catálogo. -->
        <button class="boton-carrito" id="boton-carrito" type="button" aria-expanded="false">
            Carrito <span id="contador-carrito">0</span>
        </button>
    </header>

    <!-- ─── PORTADA ────────────────────────────────────────────────────────── -->
    <section class="portada">
        <p class="etiqueta">Detalles para recordar</p>
        <h1>Todo para celebrar <em>su gran historia.</em></h1>
        <p>Encuentra piezas elegidas para bodas íntimas, celebraciones inolvidables y regalos con significado.</p>
        <a class="boton-principal" href="#productos">Explorar</a>
    </section>

    <!-- ─── CONTENIDO PRINCIPAL ────────────────────────────────────────────── -->
    <main>

        <?php if ($mensajeCompra): ?>
            <p class="aviso-compra"><?= htmlspecialchars($mensajeCompra) ?></p>
        <?php endif; ?>

        <!-- Catálogo obtenido de la tabla `productos`. -->
        <section class="seccion-productos" id="productos">
            <div class="titulo-seccion">
                <p class="etiqueta">Nuestra selección</p>
                <h2>Productos destacados</h2>
            </div>

            <div class="filtro-productos">
                <label>
                    Filtrar por categoría
                    <select id="filtro-categoria">
                        <option value="">Todas</option>
        <!--recorrer lista de categorias en base de datos -->
                    </select>
                </label>
            </div>

            <div class="rejilla-productos">
                <?php foreach ($productos as $producto): ?>
                    <?php $agotado = (int) $producto['stock'] < 1; ?>

                    <!-- Si el stock es cero, la tarjeta se conserva pero la compra se bloquea. -->
                    <article class="tarjeta-producto<?= $agotado ? ' producto-agotado' : '' ?>">

                        <div class="contenedor-imagen-producto">
                            <img
                                src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen'] ?: 'public/img/logo.jpg') ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                            >
                            <?php if ($agotado): ?>
                                <span class="sello-agotado">AGOTADO</span>
                            <?php endif; ?>
                        </div>

                        <div class="contenido-producto">
                            <span class="categoria-producto">
                                <?= htmlspecialchars($producto['categoria']) ?>
                            </span>
                            <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                            <p class="descripcion-producto">
                                <?= htmlspecialchars($producto['descripcion']) ?>
                            </p>

                            <div class="pie-producto">
                                <strong>S/ <?= number_format((float) $producto['precio'], 2) ?></strong>

                                <?php if ($agotado): ?>
                                    <button class="boton-secundario boton-deshabilitado" disabled>
                                        Agotado
                                    </button>
                                <?php else: ?>
                                    <button class="boton-secundario" data-agregar="<?= (int) $producto['id'] ?>">
                                        Añadir
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Bloque informativo de la tienda. -->
        <section class="seccion-nosotros" id="nosotros">
            <p class="etiqueta">Con cariño</p>
            <h2>Cada detalle cuenta una historia</h2>
            <p>Compra como invitado con tranquilidad. Solo pediremos tu cuenta cuando estés listo para confirmar tu pedido.</p>
        </section>

    </main>

    <!-- ─── PANEL CARRITO ──────────────────────────────────────────────────── -->
    <!-- Panel lateral para ver, modificar y eliminar productos del carrito.   -->
    <aside class="panel-carrito" id="panel-carrito" aria-hidden="true">
        <div class="cabecera-panel">
            <h2>Tu carrito</h2>
            <button class="boton-cerrar" data-cerrar="panel-carrito" aria-label="Cerrar carrito">×</button>
        </div>

        <div id="contenido-carrito" class="contenido-carrito">
            <p>Tu carrito está vacío.</p>
        </div>

        <div class="pie-carrito">
            <p>Total <strong id="total-carrito">S/ 0.00</strong></p>
            <button class="boton-principal ancho-completo" id="boton-comprar">
                Continuar con la compra
            </button>
        </div>
    </aside>

    <div class="fondo-modal" id="fondo-modal"></div>

    <!-- ─── MODAL ACCESO (invitado) ────────────────────────────────────────── -->
    <!-- Se muestra solo al pulsar "Continuar con la compra", no antes.        -->
    <section
        class="modal-acceso"
        id="modal-acceso"
        aria-hidden="true"
        role="dialog"
        aria-labelledby="titulo-acceso"
    >
        <button class="boton-cerrar" data-cerrar="modal-acceso" aria-label="Cerrar acceso">×</button>

        <!-- Vista: iniciar sesión -->
        <div id="vista-ingreso">
            <p class="etiqueta">Casi listo</p>
            <h2 id="titulo-acceso">Ingresa para finalizar tu compra</h2>
            <p>Tu carrito se mantendrá guardado.</p>

            <form id="formulario-ingreso">
                <label>
                    Correo
                    <input name="correo" type="email" required>
                </label>
                <label>
                    Contraseña
                    <input name="clave" type="password" required>
                </label>
                <button class="boton-principal ancho-completo">Iniciar sesión</button>
            </form>

            <p class="cambio-formulario">
                ¿No tienes una cuenta?
                <button data-mostrar-registro type="button">Regístrate</button>
            </p>
        </div>

        <!-- Vista: registro -->
        <div id="vista-registro" hidden>
            <p class="etiqueta">Tu cuenta</p>
            <h2>Regístrate para continuar</h2>

            <form id="formulario-registro">
                <label>
                    Nombre
                    <input name="nombre" required>
                </label>
                <label>
                    Correo
                    <input name="correo" type="email" required>
                </label>
                <label>
                    Contraseña
                    <input name="clave" type="password" minlength="6" required>
                </label>
                <button class="boton-principal ancho-completo">Crear cuenta</button>
            </form>

            <p class="cambio-formulario">
                ¿Ya tienes cuenta?
                <button data-mostrar-ingreso type="button">Inicia sesión</button>
            </p>
        </div>

        <p class="mensaje-formulario" id="mensaje-acceso"></p>
    </section>

    <!-- ─── MODAL ADMINISTRADOR ────────────────────────────────────────────── -->
    <!-- Solo accesible con Ctrl + Shift + A. El servidor valida rol admin.    -->
    <section
        class="modal-acceso"
        id="modal-administrador"
        aria-hidden="true"
        role="dialog"
        aria-labelledby="titulo-administrador"
    >
        <button class="boton-cerrar" data-cerrar="modal-administrador" aria-label="Cerrar acceso de administrador">×</button>

        <p class="etiqueta">Acceso restringido</p>
        <h2 id="titulo-administrador">Administración</h2>

        <form id="formulario-administrador">
            <label>
                Correo administrador
                <input name="correo" type="email" required>
            </label>
            <label>
                Contraseña
                <input name="clave" type="password" required>
            </label>
            <button class="boton-principal ancho-completo">Validar acceso</button>
        </form>

        <p class="mensaje-formulario" id="mensaje-administrador"></p>
    </section>
    <!-- Boton de WhatsApp para comunicacion -->
     <!-- Botón de WhatsApp -->
    <a href="https://wa.me" 
    target="51902021468" 
    style="background-color: #25D366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-flex; align-items: center; font-family: Arial, sans-serif;">
    <img src="<?= URL_BASE ?>/public/img/whatsapp.png" alt="whatsapp">
    </a>
    <!-- ─── PIE DE PÁGINA ──────────────────────────────────────────────────── -->
    <footer class="pie-pagina">
        © <?= date('Y') ?> Vínculo Bodas · Diseñado para celebrar.
    </footer>

    <script>window.URL_BASE = '<?= URL_BASE ?>';</script>
    <script src="<?= URL_BASE ?>/public/js/tienda.js"></script>

</body>
</html>