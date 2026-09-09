<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
$tokenFormulario = Autenticacion::tokenFormulario();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido | NOVIAS & QUINCE</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
</head>
<body class="seccion-checkout-cliente">

    <div class="contenedor-checkout">
        
        <a href="<?= URL_BASE ?>/inicio" class="enlace-regresar">← Modificar carrito</a>
        
        <!-- El Váucher -->
        <main class="vaucher-recibo">
            
            <div class="vaucher-cabecera">
                <p class="etiqueta vaucher-etiqueta">Resumen del Pedido</p>
                <h1>Vínculo Bodas</h1>
                <p class="vaucher-usuario">Cliente: <strong><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></strong></p>
            </div>

            <!-- DETALLE DE PRODUCTOS ADQUIRIDOS -->
            <div class="vaucher-lista">
                <?php 
                // El controlador entrega el carrito preparado para mostrar el resumen.
                if (isset($carrito) && is_array($carrito)):
                    foreach ($carrito as $item):
                ?>
                    <div class="vaucher-item">
                        <div class="vaucher-item-detalles">
                            <?= htmlspecialchars($item['nombre']) ?> 
                            <span class="vaucher-item-cantidad">x<?= (int)$item['cantidad'] ?></span>
                        </div>
                        <div class="vaucher-item-precio">
                            S/ <?= number_format(($item['precio'] * $item['cantidad']), 2) ?>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                else: 
                ?>
                    <!-- Línea de respaldo si solo manejas el total directo en esta vista por ahora -->
                    <div class="vaucher-item">
                        <div class="vaucher-item-detalles">Artículos de decoración y detalles de boda</div>
                        <div class="vaucher-item-precio">S/ <?= number_format($total, 2) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TOTAL FINAL -->
            <div class="vaucher-total-bloque">
                <span>Total a Pagar</span>
                <strong>S/ <?= number_format($total, 2) ?></strong>
            </div>

            <!-- FORMULARIO DE ACCIÓN simulado -->
            <form action="<?= URL_BASE ?>/checkout/confirmar" method="post" class="form-pago">
                <input type="hidden" name="token" value="<?= htmlspecialchars($tokenFormulario) ?>">
                <label>
                    Selecciona tu método de pago
                    <select name="metodo_pago" required>
                        <option value="yape">Yape</option>
                        <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                    </select>
                </label>
                
                <button class="boton-principal ancho-completo boton-confirmar-pedido">
                    Confirmar y Registrar Pedido
                </button>
            </form>

        </main>
    </div>

</body>
</html>
