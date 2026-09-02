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
        <title>Panel administrador | Vínculo Bodas</title>
        <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    </head>
    <body class="pagina-simple">
        <main class="panel-administracion">
            <!-- Cabecera y navegación de tareas administrativas. -->
            <header class="cabecera-admin">
                <div>
                    <p class="etiqueta">Panel protegido</p>
                    <h1>Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
                </div>
                <nav class="navegacion" aria-label="Navegación principal">
                    <a href="<?= URL_BASE ?>/inicio">Ver tienda</a>
                    <a href="<?= URL_BASE ?>/pedido">Pedidos</a>
                    <a href="<?= URL_BASE ?>/login/salir">Cerrar sesión</a>
                </nav>
            </header>
            <?php if ($mensajeAdmin): ?>
                <p class="aviso-compra"><?= htmlspecialchars($mensajeAdmin) ?></p>
            <?php endif; ?>

            <!-- Resumen rápido de existencias y ventas. -->
            <section class="tarjetas-resumen">
                <article>
                    <span>Productos visibles</span>
                    <strong><?= (int)$resumenProductos['visibles'] ?></strong>
                </article>
                <article>
                    <span>Unidades en stock</span>
                    <strong><?= (int)$resumenProductos['unidades'] ?></strong>
                </article>
                <article>
                    <span>Pedidos pendientes</span>
                    <strong><?= (int)$resumenPedidos['pendientes'] ?></strong>
                </article>
                <article>
                    <span>Ventas registradas</span>
                    <strong>S/ <?= number_format((float)$resumenPedidos['ventas'], 2) ?></strong>
                </article>
            </section>

            <!-- Lista editable del catálogo. No se borra producto para conservar el historial de pedidos. -->
            <section class="seccion-admin">
                <div class="titulo-admin">
                    <div>
                        <p class="etiqueta">Catálogo</p>
                        <h2>Productos y stock</h2>
                    </div>
                    <a class="boton-principal" href="<?= URL_BASE ?>/producto/crear">Agregar producto</a>
                </div>
                <div class="tabla-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                    <td><small><?= htmlspecialchars($producto['categoria']) ?></small></td>
                                    <td class="<?= (int)$producto['stock'] < 1 ? 'stock-agotado' : '' ?>"><?= (int)$producto['stock'] ?></td>
                                    <td>S/ <?= number_format((float)$producto['precio'], 2) ?></td>
                                    <td><?= $producto['activo'] ? 'Visible' : 'Oculto' ?></td>
                                    <td><a class="boton-secundario boton-pequeno" href="<?= URL_BASE ?>/producto/editar/<?= (int)$producto['id'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bitácora de cambios ejecutados por administradores.
            <section class="seccion-admin">
                <div class="titulo-admin">
                    <div>
                        <p class="etiqueta">Registro</p>
                        <h2>Última actividad</h2>
                    </div>
                </div>
                <div class="lista-registros">
                    <?php /*if (!$registros): ?>
                        <p>Aún no hay cambios registrados.</p>
                    <?php endif; ?>
                    <?php foreach ($registros as $registro): ?>
                        <article>
                            <strong><?= htmlspecialchars($registro['nombre']) ?></strong>
                            <span><?= htmlspecialchars($registro['accion']) ?>: <?= htmlspecialchars($registro['detalle']) ?></span>
                            <time><?= htmlspecialchars($registro['creado_en']) ?></time>
                        </article>
                    <?php endforeach; */?>
                </div>
            </section>
            -->
        </main>
    </body>
</html>
