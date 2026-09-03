<?php require_once __DIR__ . '/../helpers/Autenticacion.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido | Vínculo Bodas</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/styles1.css">
    <style>
        .seccion-checkout-cliente {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 40px 16px;
            background: var(--crema);
        }
        .contenedor-checkout {
            width: min(550px, 100%);
        }
        .enlace-regresar {
            color: var(--suave);
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .enlace-regresar:hover {
            color: var(--vino);
        }
        
        /* Estilo del Váucher Premium */
        .vaucher-recibo {
            background: var(--blanco);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(122, 40, 71, 0.08);
            border: 1px solid #efd9d1;
            padding: 35px 30px;
            position: relative;
        }
        /* Efecto de corte de ticket clásico en la parte inferior */
        .vaucher-recibo::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 8px;
            background-image: linear-gradient(-45deg, var(--crema) 4px, transparent 0), linear-gradient(45deg, var(--crema) 4px, transparent 0);
            background-size: 8px 8px;
        }
        
        .vaucher-cabecera {
            text-align: center;
            border-bottom: 2px dashed #f6e7df;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .vaucher-cabecera h1 {
            font-family: Georgia, serif;
            color: var(--vino-oscuro);
            font-size: 1.8rem;
            margin: 5px 0 0 0;
        }
        .vaucher-usuario {
            color: var(--suave);
            font-size: 0.95rem;
            margin: 10px 0 0 0;
        }
        .vaucher-usuario strong {
            color: var(--texto);
        }

        /* Lista de Productos del Váucher */
        .vaucher-lista {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 25px;
        }
        .vaucher-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 0.95rem;
            gap: 15px;
        }
        .vaucher-item-detalles {
            color: var(--texto);
        }
        .vaucher-item-cantidad {
            color: var(--suave);
            font-size: 0.85rem;
            font-weight: bold;
        }
        .vaucher-item-precio {
            color: var(--vino-oscuro);
            font-weight: 700;
            white-space: nowrap;
        }

        /* Bloque de Totales */
        .vaucher-total-bloque {
            border-top: 2px dashed #f6e7df;
            padding-top: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .vaucher-total-bloque span {
            font-size: 1.1rem;
            color: var(--suave);
            font-weight: bold;
        }
        .vaucher-total-bloque strong {
            font-size: 1.5rem;
            color: var(--vino);
            font-family: Georgia, serif;
        }

        /* Formulario de Pago integrado */
        .form-pago {
            display: grid;
            gap: 20px;
        }
    </style>
</head>
<body class="seccion-checkout-cliente">

    <div class="contenedor-checkout">
        
        <a href="<?= URL_BASE ?>/inicio" class="enlace-regresar">← Modificar carrito</a>
        
        <!-- El Váucher -->
        <main class="vaucher-recibo">
            
            <div class="vaucher-cabecera">
                <p class="etiqueta" style="margin: 0;">Resumen del Pedido</p>
                <h1>Vínculo Bodas</h1>
                <p class="vaucher-usuario">Cliente: <strong><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></strong></p>
            </div>

            <!-- DETALLE DE PRODUCTOS ADQUIRIDOS -->
            <div class="vaucher-lista">
                <?php 
                // Asumimos que los artículos del carrito vienen en un array $itemsCarrito.
                // Si aún no tienes esa variable estructurada, puedes simularla temporalmente o iterar tu variable de sesión.
                if (isset($itemsCarrito) && is_array($itemsCarrito)): 
                    foreach ($itemsCarrito as $item): 
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
                <label>
                    Selecciona tu método de pago
                    <select name="metodo_pago" required>
                        <option value="yape">Yape</option>
                        <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                    </select>
                </label>
                
                <button class="boton-principal ancho-completo" style="padding: 15px; font-size: 1rem;">
                    Confirmar y Registrar Pedido
                </button>
            </form>

        </main>
    </div>

</body>
</html>
