<?php
$mensajeAdmin = $_SESSION['mensaje_admin'] ?? '';
unset($_SESSION['mensaje_admin']);
$token = Autenticacion::tokenFormulario();
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Panel Administrador | Vínculo Bodas</title>
        <!-- Tu CSS original se mantiene si es necesario, pero añadimos el nuevo exclusivo para el Admin -->
        <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
        <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
    </head>
    <body class="body-admin">
        
        <div class="contenedor-dashboard">
            
            <!-- 1. MENÚ LATERAL (SIDEBAR) -->
            <aside class="sidebar-admin">
                <div class="sidebar-marca">
                    <h2>Vínculo Bodas</h2>
                    <span class="etiqueta">Panel Protegido</span>
                </div>
                
                <div class="sidebar-usuario">
                    <div class="avatar">👤</div>
                    <div class="usuario-info">
                        <p class="usuario-hola">Hola,</p>
                        <p class="usuario-nombre"><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></p>
                    </div>
                </div>

                <nav class="navegacion-sidebar" aria-label="Navegación principal">
                    <a href="<?= URL_BASE ?>/inicio" class="nav-link"><span>🏪</span> Ver tienda</a>
                    <a href="<?= URL_BASE ?>/pedido" class="nav-link"><span>📦</span> Pedidos</a>
                    <a href="<?= URL_BASE ?>/login/salir" class="nav-link salir"><span>🚪</span> Cerrar sesión</a>
                </nav>
            </aside>

            <!-- 2. CONTENIDO PRINCIPAL -->
            <main class="contenido-principal">
                
                <!-- Notificaciones de Sistema -->
                <?php if ($mensajeAdmin): ?>
                    <div class="alerta-admin aviso-compra">
                        <span>ℹ️</span> <?= htmlspecialchars($mensajeAdmin) ?>
                    </div>
                <?php endif; ?>

                <!-- Sección: Resumen en Tarjetas -->
                <section class="grid-tarjetas">
                    <article class="tarjeta-metrica">
                        <div class="tarjeta-icono">👁️</div>
                        <div class="tarjeta-datos">
                            <span>Productos visibles</span>
                            <strong><?= (int)$resumenProductos['visibles'] ?></strong>
                        </div>
                    </article>
                    <article class="tarjeta-metrica">
                        <div class="tarjeta-icono">📦</div>
                        <div class="tarjeta-datos">
                            <span>Unidades en stock</span>
                            <strong><?= (int)$resumenProductos['unidades'] ?></strong>
                        </div>
                    </article>
                    <article class="tarjeta-metrica pendientes">
                        <div class="tarjeta-icono">⏳</div>
                        <div class="tarjeta-datos">
                            <span>Pedidos pendientes</span>
                            <strong><?= (int)$resumenPedidos['pendientes'] ?></strong>
                        </div>
                    </article>
                    <article class="tarjeta-metrica ventas">
                        <div class="tarjeta-icono">💰</div>
                        <div class="tarjeta-datos">
                            <span>Ventas registradas</span>
                            <strong>S/ <?= number_format((float)$resumenPedidos['ventas'], 2) ?></strong>
                        </div>
                    </article>
                </section>

                <!-- Sección: Tabla de Gestión de Catálogo -->
                <section class="tarjeta-blanca-seccion">
                    <div class="encabezado-tabla">
                        <div>
                            <span class="subtitulo-seccion">Catálogo</span>
                            <h2>Productos y stock</h2>
                        </div>
                        <a class="boton-admin-principal" href="<?= URL_BASE ?>/producto/crear">+ Agregar producto</a>
                    </div>
                    
                    <div class="contenedor-tabla-responsive">
                        <table class="tabla-admin">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th class="texto-derecha">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td class="col-nombre-producto"><?= htmlspecialchars($producto['nombre']) ?></td>
                                        <td><span class="badge-categoria"><?= htmlspecialchars($producto['categoria']) ?></span></td>
                                        <td>
                                            <span class="badge-stock <?= (int)$producto['stock'] < 1 ? 'agotado' : 'con-stock' ?>">
                                                <?= (int)$producto['stock'] ?> unds
                                            </span>
                                        </td>
                                        <td class="col-precio">S/ <?= number_format((float)$producto['precio'], 2) ?></td>
                                        <td>
                                            <span class="badge-estado <?= $producto['activo'] ? 'visible' : 'oculto' ?>">
                                                <?= $producto['activo'] ? 'Visible' : 'Oculto' ?>
                                            </span>
                                        </td>
                                        <td class="texto-derecha">
                                            <a class="boton-admin-editar" href="<?= URL_BASE ?>/producto/editar/<?= (int)$producto['id'] ?>">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </main>
        </div>

    </body>
</html>
