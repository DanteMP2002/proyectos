<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/RegistroAdmin.php';

// Panel inicial protegido; desde aquí se podrá ampliar el mantenimiento.
class AdminControlador {
    public function index(): void {
        Autenticacion::exigirAdministrador();
        $productoModel = new Producto();
        $pedidoModel = new Pedido();
        $productos = $productoModel->listarTodos();
        $resumenProductos = $productoModel->resumenAdministrativo();
        $resumenPedidos = $pedidoModel->resumenAdministrativo();
        $registros = (new RegistroAdmin())->ultimos();
        require __DIR__ . '/../views/admin.php';
    }
}
