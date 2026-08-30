<?php
require_once __DIR__ . '/../models/Producto.php';

// Página pública: no pide sesión para navegar ni añadir al carrito.
class InicioControlador {
    public function index(): void {
        $productos = (new Producto())->listarDisponibles();
        require __DIR__ . '/../views/inicio.php';
    }
}
