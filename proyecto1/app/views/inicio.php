<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';

$mensajeCompra = $_SESSION['mensaje_compra'] ?? '';
unset($_SESSION['mensaje_compra']);
$tokenFormulario = Autenticacion::tokenFormulario();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda |  MUNDO NOVIAS <span>& QUINCE</span></title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css?v=<?= filemtime(__DIR__ . '/../..' . '/public/css/styles1.css') ?>">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/modal.css">
</head>
<body>

    <!-- ─── ENCABEZADO ─────────────────────────────────────────────────────── -->
    <header class="encabezado-principal">
        <a class="marca" href="<?= URL_BASE ?>/inicio">
            MUNDO NOVIAS <span>& QUINCE</span>
        </a>

        <nav class="navegacion" aria-label="Navegación principal">
            <a href="#productos">Productos</a>
            <a href="#nosotros">Nosotros</a>
            
            <?php if (Autenticacion::esAdministrador()): ?>
                <a href="<?= URL_BASE ?>/admin">Panel</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['usuario'])): ?>
                <!-- Si el usuario está logueado, ve su nombre y la opción de salir -->
                <span class="usuario-conectado">
                    👤 <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                </span>

                <!-- NUEVO ENLACE PARA EL CLIENTE -->
                <a class="enlace-pedidos" href="<?= URL_BASE ?>/pedido/mispedidos">
                    Mis Pedidos
                </a>

                <form action="<?= URL_BASE ?>/login/salir" method="post" class="formulario-salir">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($tokenFormulario) ?>">
                    <button class="enlace-salir" type="submit">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <span class="acceso-navegacion" aria-label="Acceso a cuenta">
                    <button class="enlace-ingreso" data-mostrar-ingreso id="enlace-login-nav" type="button">Iniciar sesión</button>
                    <span aria-hidden="true">|</span>
                    <button data-mostrar-registro class="cambio-formulario enlace-ingreso"  id="enlace-registro-nav" type="button">Registrarse</button>
                </span>
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

            <!-- ─── NUEVA BARRA DE FILTROS COMBINADOS ─── -->
            <div class="filtro-productos" aria-label="Filtros del catálogo">
                
                <!-- Buscador por Texto -->
                <div class="campo-filtro">
                    <label for="buscador-texto">Buscar producto</label>
                    <input type="text" id="buscador-texto" placeholder="Escribe el nombre del producto...">
                </div>

                <!-- Filtro por Categoría -->
                <div class="campo-filtro">
                    <label for="filtro-categoria">Filtrar por categoría</label>
                    <select id="filtro-categoria">
                        <option value="">Todas las categorías</option>
                        <?php 
                        // Extraemos las categorías únicas que existen en tu array de productos actual de PHP
                        $categorias_unicas = array_unique(array_column($productos, 'categoria'));
                        foreach ($categorias_unicas as $cat): 
                            if(!empty($cat)):
                        ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>

                <div class="campo-filtro campo-orden">
                    <label for="orden-catalogo">Ordenar productos</label>
                    <select id="orden-catalogo">
                        <option value="">Recomendados</option>
                        <option value="menor-mayor">Precio</option>
                        <option value="nombre">Nombre: A a Z</option>
                    </select>
                </div>
                <button id="invertir-orden" class="boton-invertir-orden" type="button" title="Invertir el orden" aria-label="Invertir el orden">↕</button>
            </div>

            <!-- ─── REJILLA DE PRODUCTOS (Ajustada con data-attributes para JS) ─── -->
            <div class="rejilla-productos" id="contenedor-productos">
                <?php foreach ($productos as $producto): ?>
                    <?php $agotado = (int) $producto['stock'] < 1; ?>
                    <?php
                    $imagenesCatalogo = array_map(
                        static function (array $imagen): string {
                            $ruta = ltrim($imagen['ruta_imagen'], '/');
                            $archivo = __DIR__ . '/../../' . $ruta;
                            $version = is_file($archivo) ? (string) filemtime($archivo) : '0';
                            return URL_BASE . '/' . $ruta . '?v=' . $version;
                        },
                        $producto['imagenes'] ?? []
                    );
                    if (!$imagenesCatalogo && !empty($producto['imagen'])) {
                        $ruta = ltrim($producto['imagen'], '/');
                        $archivo = __DIR__ . '/../../' . $ruta;
                        $version = is_file($archivo) ? (string) filemtime($archivo) : '0';
                        $imagenesCatalogo[] = URL_BASE . '/' . $ruta . '?v=' . $version;
                    }
                    // Se codifica para que URLs con &, comillas o parámetros de versión
                    // lleguen intactas a JavaScript al abrir la galería.
                    $imagenesJson = rawurlencode(json_encode($imagenesCatalogo, JSON_UNESCAPED_SLASHES));
                    ?>
                    <?php
                    // Construimos el enlace dinámico de WhatsApp con el nombre del producto
                        $nombre_producto = $producto['nombre']; 
                        $telefono = "51920134856"; 
                        $mensaje = "Buen dia, me interesa el *" . $nombre_producto . "* y quisiera mas informacion.";
                        $enlace_dinamico = "https://wa.me/" . $telefono . "?text=" . rawurlencode($mensaje);
                    ?>

                    <!-- Los atributos data-* permiten abrir el detalle sin consultar otra vez al servidor. -->
                    <article class="tarjeta-producto<?= $agotado ? ' producto-agotado' : '' ?>" 
                        data-id="<?= (int) $producto['id'] ?>"
                        data-nombre="<?= htmlspecialchars($producto['nombre']) ?>"
                        data-descripcion="<?= htmlspecialchars($producto['descripcion']) ?>"
                        data-whatsapp="<?= $enlace_dinamico ?>"
                        data-categoria="<?= htmlspecialchars($producto['categoria']) ?>"
                        data-precio="<?= (float)$producto['precio'] ?>"
                        data-imagen="<?= htmlspecialchars($imagenesCatalogo[0] ?? URL_BASE . '/public/img/banner2.png') ?>"
                        data-imagenes="<?= $imagenesJson ?>"
                        data-agotado="<?= $agotado ? '1' : '0' ?>"
                    >
                        <button class="boton-ver-detalle" type="button" data-ver-producto aria-label="Ver detalle de <?= htmlspecialchars($producto['nombre']) ?>">
                            <span class="contenedor-imagen-producto">
                                <img src="<?= htmlspecialchars($imagenesCatalogo[0] ?? URL_BASE . '/public/img/logo.jpg') ?>"
                                    alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                <span class="categoria-superpuesta"><?= htmlspecialchars($producto['categoria']) ?></span>
                                <?php if ($agotado): ?>
                                    <span class="sello-agotado">AGOTADO</span>
                                <?php endif; ?>
                            </span>
                        </button>
                        <?php if ($agotado): ?>
                            <a class="boton-consulta-agotado" href="<?= $enlace_dinamico ?>" target="_blank" rel="noopener">Consultar disponibilidad</a>
                        <?php endif; ?>
                        <template class="datos-imagenes-producto">
                            <?php foreach ($imagenesCatalogo as $imagenCatalogo): ?>
                                <img src="<?= htmlspecialchars($imagenCatalogo) ?>" alt="">
                            <?php endforeach; ?>
                        </template>
                    </article>

                <?php endforeach; ?>
            </div>
        </section>

        <!-- Un solo modal reutilizable. JavaScript llena sus datos al pulsar una tarjeta. -->
        <section id="modal-producto" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-nombre" aria-hidden="true">
            <div class="modal-caja">
                <div class="modal-imagen-envoltorio">
                    <div class="modal-imagen-visor">
                        <img id="modal-imagen" src="" alt="">
                        <span id="modal-contador-imagenes" class="modal-contador-imagenes" hidden></span>
                    </div>
                    <div id="modal-galeria" class="modal-galeria" aria-label="Más imágenes del producto">
                        
                    </div>
                    <span id="modal-categoria" class="modal-etiqueta-categoria"></span>
                    <button class="modal-cerrar" type="button" data-cerrar-producto aria-label="Cerrar detalle">×</button>
                </div>
                <div class="modal-cuerpo">
                    <h2 id="modal-nombre" class="modal-nombre"></h2>
                    <p id="modal-descripcion" class="modal-descripcion"></p>
                    <hr class="modal-separador">
                    <div class="modal-fila-precio"><span class="modal-etiqueta-precio">Precio</span><strong id="modal-precio" class="modal-precio-valor"></strong></div>
                    <div class="modal-acciones">
                        <button id="modal-btn-carrito" class="modal-boton-carrito" type="button">Añadir al carrito</button>
                        <a id="modal-whatsapp" class="modal-boton-whatsapp" target="_blank" rel="noopener">Consultar por WhatsApp</a>
                    </div>
                </div>
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
                <input type="hidden" name="token" value="<?= htmlspecialchars($tokenFormulario) ?>">
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
                <input type="hidden" name="token" value="<?= htmlspecialchars($tokenFormulario) ?>">
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
            <input type="hidden" name="token" value="<?= htmlspecialchars($tokenFormulario) ?>">
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
    <!-- Botón de WhatsApp Arrastrable -->
     <? $mensajeBoton = "Hola, he visitado tu tienda. Quisiera mas informacion."?>
    <a href="https://wa.me/51920134856?text=<?= rawurlencode($mensajeBoton) ?>" target="_blank" class="boton-whatsapp-fijo" id="whatsapp-flotante">
        <img class="imagen-whatsapp-fijo" src="<?= URL_BASE ?>/public/img/whatsapp.jpg" alt="whatsapp" draggable="false">
    </a>

    <!-- ─── PIE DE PÁGIN A ──────────────────────────────────────────────────── -->
    <footer class="pie-pagina">
        <div class="pie-pagina-contenido">
            <div class="pie-marca">
                <a class="marca" href="#inicio">MUNDO NOVIAS <span>&amp; QUINCE</span></a>
                <p>Detalles elegidos para celebrar momentos que se quedan para siempre.</p>
            </div>
            <div class="pie-enlaces">
                <h2>Explora</h2>
                <a href="#productos">Catálogo</a>
                <a href="#nosotros">Nosotros</a>
                <?php if (Autenticacion::iniciado()): ?>
                    <a href="<?= URL_BASE ?>/pedido/mispedidos">Mis pedidos</a>
                <?php endif; ?>
            </div>
            <div class="pie-contacto">
                <h2>¿Necesitas ayuda?</h2>
                <p>Escríbenos y te ayudamos a elegir.</p>
                <a href="https://wa.me/51920134856" target="_blank" rel="noopener">WhatsApp ↗</a>
                <div class="redes-pie" aria-label="Redes sociales">
                    <span class="icono-red-social" title="Instagram" aria-label="Instagram">o</span>
                    <span class="icono-red-social" title="Facebook" aria-label="Facebook">f</span>
                    <span class="icono-red-social" title="TikTok" aria-label="TikTok">♪</span>
                </div>
            </div>
        </div>
        <div class="pie-legal">© <?= date('Y') ?> Mundo Novias &amp; Quince. Hecho para celebrar.</div>
    </footer>

    <script>window.URL_BASE = '<?= URL_BASE ?>';</script>
    <script src="<?= URL_BASE ?>/public/js/tienda.js"></script>
    <script src="<?= URL_BASE ?>/public/js/catalogo.js?v=<?= filemtime(__DIR__ . '/../..' . '/public/js/catalogo.js') ?>"></script>
    <script src="<?= URL_BASE ?>/public/js/modal.js?v=<?= filemtime(__DIR__ . '/../..' . '/public/js/modal.js') ?>"></script>

</body>
</html>
