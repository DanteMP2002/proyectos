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
    <title>Vínculo Bodas | Tienda</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
</head>
<body>

    <!-- ─── ENCABEZADO ─────────────────────────────────────────────────────── -->
    <header class="encabezado-principal">
        <a class="marca" href="<?= URL_BASE ?>/inicio">
            Vínculo <span>Bodas</span>
        </a>

        <nav class="navegacion" aria-label="Navegación principal" style="display: flex; align-items: center; gap: 20px;">
            <a href="#productos">Productos</a>
            <a href="#nosotros">Nosotros</a>
            
            <?php if (Autenticacion::esAdministrador()): ?>
                <a href="<?= URL_BASE ?>/admin">Panel</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['usuario'])): ?>
                <!-- Si el usuario está logueado, ve su nombre y la opción de salir -->
                <span style="color: var(--vino-oscuro); font-weight: 700;">
                    👤 <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                </span>

                <!-- NUEVO ENLACE PARA EL CLIENTE -->
                <a href="<?= URL_BASE ?>/pedido/mispedidos" style="color: var(--vino); font-weight: 700; margin-left: 10px; text-decoration: underline;">
                    Mis Pedidos
                </a>

                <a href="<?= URL_BASE ?>/login/salir" style="color: #e74c3c; font-weight: 700; transition: color 0.2s;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'">
                    Cerrar sesión
                </a>
            <?php else: ?>
                <!-- Si es invitado, ve la opción de ingresar (puedes enlazarlo a tu disparador de modal JS) -->
                <button data-mostrar-ingreso id="enlace-login-nav" style="color: var(--vino); font-weight: 700;">
                    Iniciar sesión / Registrarse
                </button>
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
            <div class="filtro-productos" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 30px; background: #fffdfc; padding: 20px; border-radius: 8px; border: 1px solid #ebd6ce;">
                
                <!-- Buscador por Texto -->
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.85rem; color: #745f65;">Buscar producto</label>
                    <input type="text" id="buscador-texto" placeholder="Escribe el nombre del producto...">
                </div>

                <!-- Filtro por Categoría -->
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.85rem; color: #745f65;">Filtrar por categoría</label>
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

                <!-- Ordenar por Precio -->
                <div style="flex: 1; min-width: 180px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.85rem; color: #745f65;">Ordenar por precio</label>
                    <select id="orden-precio">
                        <option value="">Recomendados</option>
                        <option value="menor-mayor">Precio: Menor a Mayor</option>
                        <option value="mayor-menor">Precio: Mayor a Menor</option>
                    </select>
                </div>

            </div>

            <!-- ─── REJILLA DE PRODUCTOS (Ajustada con data-attributes para JS) ─── -->
            <div class="rejilla-productos" id="contenedor-productos">
                <?php foreach ($productos as $producto): ?>
                    <?php $agotado = (int) $producto['stock'] < 1; ?>
                    <?php
                    // Construimos el enlace dinámico de WhatsApp con el nombre del producto
                        $nombre_producto = $producto['nombre']; 
                        $telefono = "51920134856"; 
                        $mensaje = "Buen dia, me interesa el *" . $nombre_producto . "* y quisiera mas informacion.";
                        $enlace_dinamico = "https://wa.me/" . $telefono . "?text=" . rawurlencode($mensaje);
                    ?>

                    <!-- Agregamos data-categoria, data-nombre y data-precio para que JavaScript pueda leerlos -->
                     <!-- Si el stock es cero, la tarjeta se conserva pero la compra se bloquea. -->
                      
                    <article class="tarjeta-producto<?= $agotado ? ' producto-agotado' : '' ?>" 
                        data-nombre="<?= htmlspecialchars(mb_strtolower($producto['nombre'])) ?>"
                        data-descripcion="<?= htmlspecialchars($producto['descripcion']) ?>"
                        data-whatsapp="<?= $enlace_dinamico ?>"
                        data-categoria="<?= htmlspecialchars($producto['categoria']) ?>"
                        data-precio="<?= (float)$producto['precio'] ?>"
                        data-imagen="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen'] ?: 'public/img/banner2.png') ?>"
                        onclick="abrirModal(this)"
                    >
                            
                        <div class="contenedor-imagen-producto" onclick="abrirModal(this)">
                            <img src="<?= URL_BASE ?>/<?= htmlspecialchars($producto['imagen'] ?: 'public/img/logo.jpg') ?>" 
                                alt="<?= htmlspecialchars($producto['nombre']) ?>" onclick="abrirModal(this)">
                            <?php if ($agotado): ?>
                                <span class="sello-agotado">AGOTADO</span>
                            <?php endif; ?>
                        </div>
                        
                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                    </article>

                <?php endforeach; ?>
            </div>
        </section>

<!-- Modal reutilizable -->
<div style="display: flex; flex-direction: column; gap: 12px; border-top: 1px solid #efd9d1; padding-top: 12px;"
    id="modal-producto" class="modal" >
    <div style="display: grid; justify-content: space-between; align-items: center;"
        class="modal-contenido">
                <span class="cerrar" onclick="cerrarModal()">&times;</span>
                <h2 id="modal-nombre"></h2>
                <p id="modal-descripcion"></p>
                <!-- PRECIO -->                       
                <span style="color: var(--suave); font-size: 0.85rem; font-weight: bold;">Precio:</span>
                <strong style="font-size: 1.3rem; color: var(--vino); white-space: nowrap; display: inline-block;">
                    S/ <strong id="modal-precio"></strong>
                </strong>
        
                <a id="modal-whatsapp" target="_blank" class="boton-whatsapp">Consultar por WhatsApp</a>
    </div>
</div>

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
    <!-- Botón de WhatsApp Arrastrable -->
    <a href="https://wa.me/51920134856?text=Buen%20dia,%20quiero%20%20mas%20informacion%20sobre%20los%20productos%20de%20su%20tienda." target="_blank" class="boton-whatsapp-fijo" id="whatsapp-flotante">
        <img src="<?= URL_BASE ?>/public/img/whatsapp.png" alt="whatsapp" draggable="false" style="width: 50px; height: 50px; display: block;">
    </a>

    <!-- ─── PIE DE PÁGINA ──────────────────────────────────────────────────── -->
    <footer class="pie-pagina">
        © <?= date('Y') ?> Vínculo Bodas · Diseñado para celebrar.
    </footer>

    <script>window.URL_BASE = '<?= URL_BASE ?>';</script>
    <script src="<?= URL_BASE ?>/public/js/tienda.js"></script>

</body>
</html>