<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos | NOVIAS & QUINCE</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
</head>
<body class="pagina-cliente">
    <!-- Historial disponible únicamente para el usuario que inició sesión. -->
    <main class="contenedor-pagina contenedor-pedidos">
        <header class="cabecera-pagina">
            <div>
                <p class="etiqueta">Tu cuenta</p>
                <h1>Mis pedidos</h1>
                <p class="texto-ayuda">Hola, <strong><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></strong>. Aquí puedes revisar el estado de cada compra.</p>
            </div>
            <a href="<?= URL_BASE ?>/inicio" class="boton-secundario">Volver a la tienda</a>
        </header>

        <section class="tarjeta-contenido" aria-labelledby="titulo-historial">
            <h2 id="titulo-historial" class="visualmente-oculto">Historial de pedidos</h2>
            <?php if (empty($pedidosCliente)): ?>
                <!-- Mensaje mostrado cuando el cliente todavía no registró compras. -->
                <div class="estado-vacio">
                    <h2>Aún no tienes pedidos</h2>
                    <p>Explora el catálogo cuando quieras preparar tu celebración.</p>
                    <a href="<?= URL_BASE ?>/inicio#productos" class="boton-principal">Ver productos</a>
                </div>
            <?php else: ?>
                <div class="tabla-responsive">
                    <table class="tabla-pedidos">
                        <thead><tr><th>Código</th><th>Fecha</th><th>Pago</th><th>Total</th><th>Estado</th><th><span class="visualmente-oculto">Acciones</span></th></tr></thead>
                        <tbody>
                            <?php foreach ($pedidosCliente as $pedido): ?>
                                <?php $estado = $pedido['estado'] ?? 'pendiente'; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pedido['codigo'] ?? (string) $pedido['id']) ?></strong></td>
                                    <td><?= htmlspecialchars($pedido['creado_en'] ?? $pedido['fecha']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($pedido['metodo_pago'])) ?></td>
                                    <td><strong>S/ <?= number_format((float) $pedido['total'], 2) ?></strong></td>
                                    <td><span class="estado-pedido estado-<?= htmlspecialchars($estado) ?>"><?= htmlspecialchars(ucfirst($estado)) ?></span></td>
                                    <td><a class="enlace-accion" href="<?= URL_BASE ?>/pedido/detalle/<?= (int) $pedido['id'] ?>">Ver detalle</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
