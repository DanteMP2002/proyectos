<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/RegistroAdmin.php';

// Mantenimiento de catálogo. Todas sus acciones requieren ser administrador.
class ProductoControlador {
    private Producto $productos;
    private RegistroAdmin $registro;
    private string $carpetaImagenes;

    public function __construct() {
        Autenticacion::exigirAdministrador();
        $this->productos = new Producto();
        $this->registro = new RegistroAdmin();
        $this->carpetaImagenes = __DIR__ . '/../../public/img/productos/';
    }

    private function volver(string $mensaje): never {
        $_SESSION['mensaje_admin'] = $mensaje;
        header('Location: ' . URL_BASE . '/admin');
        exit;
    }

    private function exigirPostSeguro(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }
    }

    private function datosFormulario(string $imagen): array {
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        if ($nombre === '' || $categoria === '' || $descripcion === '') throw new RuntimeException('Completa nombre, categoría y descripción.');
        return [
            'nombre' => $nombre, 'categoria' => $categoria, 'descripcion' => $descripcion,
            'precio' => max(0, (float)($_POST['precio'] ?? 0)), 'stock' => max(0, (int)($_POST['stock'] ?? 0)),
            'imagen' => $imagen, 'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    // Valida tipo y tamaño para que solo se guarden imágenes reales de producto.
    private function guardarImagen(?string $imagenActual = null): string {
        if (empty($_FILES['imagen']['name']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $imagenActual ?? '';
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK || $_FILES['imagen']['size'] > 5 * 1024 * 1024) throw new RuntimeException('La imagen debe pesar como máximo 5 MB.');
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagen']['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensiones[$tipo])) throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        if (!is_dir($this->carpetaImagenes)) mkdir($this->carpetaImagenes, 0755, true);
        $nombre = bin2hex(random_bytes(12)) . '.' . $extensiones[$tipo];
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $this->carpetaImagenes . $nombre)) throw new RuntimeException('No se pudo guardar la imagen.');
        return 'public/img/productos/' . $nombre;
    }

    public function crear(): void {
        $producto = ['nombre' => '', 'categoria' => '', 'descripcion' => '', 'precio' => '', 'stock' => 0, 'imagen' => '', 'activo' => 1];
        $titulo = 'Agregar producto';
        $accion = URL_BASE . '/producto/guardar';
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function guardar(): void {
        $this->exigirPostSeguro();
        try {
            $datos = $this->datosFormulario($this->guardarImagen());
            $id = $this->productos->crear($datos);
            $this->registro->guardar($_SESSION['usuario']['id'], 'Creó producto', $datos['nombre'] . ' (#' . $id . ')');
            $this->volver('Producto creado correctamente.');
        } catch (Throwable $error) { $this->volver($error->getMessage()); }
    }

    public function editar(int $id): void {
        $producto = $this->productos->buscar($id);
        if (!$producto) $this->volver('Producto no encontrado.');
        $titulo = 'Editar producto';
        $accion = URL_BASE . '/producto/actualizar/' . $id;
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function actualizar(int $id): void {
        $this->exigirPostSeguro();
        $actual = $this->productos->buscar($id);
        if (!$actual) $this->volver('Producto no encontrado.');
        try {
            $datos = $this->datosFormulario($this->guardarImagen($actual['imagen']));
            $this->productos->actualizar($id, $datos);
            $this->registro->guardar($_SESSION['usuario']['id'], 'Editó producto', $datos['nombre'] . ' (#' . $id . ')');
            $this->volver('Producto actualizado correctamente.');
        } catch (Throwable $error) { $this->volver($error->getMessage()); }
    }
}
