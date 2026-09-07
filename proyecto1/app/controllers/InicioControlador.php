<?php
require_once __DIR__ . '/../models/Producto.php';

/** Carga el catálogo público de productos visibles. */
class InicioControlador
{
    private Producto $productos;

    public function __construct()
    {
        $this->productos = new Producto();
    }

    public function index(): void
    {
        $productos = $this->productos->listarDisponibles();
        require __DIR__ . '/../views/inicio.php';
    }
}
