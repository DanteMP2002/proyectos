<?php $token = Autenticacion::tokenFormulario(); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos | Administración</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/stylesAdmin.css">
</head>
<body class="body-admin">
    <div class="contenedor-dashboard">
        
        <!-- SIDEBAR DE NAVEGACIÓN -->
        <aside class="sidebar-admin">
            <div class="sidebar-marca">
                <h2>Vínculo Bodas</h2>
                <span class="etiqueta">Administración</span>
            </div>
            <nav class="navegacion-sidebar" aria-label="Navegación principal">
                <a href="<?= URL_BASE ?>/admin" class="nav-link"><span>📊</span> Panel Catálogo</a>
                <a href="<?= URL_BASE ?>/pedido" class="nav-link"><span>📦</span> Pedidos</a>
                <a href="<?= URL_BASE ?>/inicio" class="nav-link"><span>🏪</span> Ver tienda</a>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="contenido-principal">
            <section class="tarjeta-blanca-seccion">
                <div class="encabezado-tabla">
                    <div>
                        <span class="subtitulo-seccion">Gestión</span>
                        <h2>Pedidos recibidos</h2>
                    </div>
                </div>

                <div class="contenedor-tabla-responsive">
                    <table class="tabla-admin">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Estado</th>
                                <th class="texto-derecha">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td class="col-nombre-producto">
                                        <?= htmlspecialchars($pedido['codigo']) ?>
                                        <br><small style="color: var(--texto-gris);"><?= htmlspecialchars($pedido['creado_en']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($pedido['cliente']) ?>
                                        <br><small style="color: var(--texto-gris);"><?= htmlspecialchars($pedido['correo']) ?></small>
                                    </td>
                                    <td class="col-precio">S/ <?= number_format((float)$pedido['total'], 2) ?></td>
                                    <td><span class="badge-categoria"><?= htmlspecialchars(ucfirst($pedido['metodo_pago'])) ?></span></td>
                                    <td>
                                        <span class="badge-estado <?= htmlspecialchars($pedido['estado']) ?>">
                                            <?= htmlspecialchars(ucfirst($pedido['estado'])) ?>
                                        </span>
                                    </td>
                                    <td class="texto-derecha">
                                        <a class="boton-admin-editar" href="<?= URL_BASE ?>/pedido/detalle/<?= (int)$pedido['id'] ?>">Ver</a>
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
