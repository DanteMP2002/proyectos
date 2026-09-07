<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Pedido.php';

/** Muestra el resumen administrativo de productos y pedidos. */
class AdminControlador
{
    public function index(): void
    {
        Autenticacion::exigirAdministrador();

        $productosModelo = new Producto();
        $pedidosModelo = new Pedido();

        $productos = $productosModelo->listarTodos();
        $resumenProductos = $productosModelo->resumenAdministrativo();
        $resumenPedidos = $pedidosModelo->resumenAdministrativo();

        // No se consulta RegistroAdmin: la tabla registros_admin no existe en el SQL actual.
        require __DIR__ . '/../views/admin.php';
    }
}
