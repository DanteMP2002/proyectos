<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';

// Panel inicial protegido; desde aquí se podrá ampliar el mantenimiento.
class AdminControlador {
    public function index(): void {
        Autenticacion::exigirAdministrador();
        $productos = (new Producto())->listarTodos();
        require __DIR__ . '/../views/admin.php';
    }
}
