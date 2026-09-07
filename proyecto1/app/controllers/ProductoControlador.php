<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';

/** Gestiona el catálogo. Todas las rutas requieren rol administrador. */
class ProductoControlador
{
    private Producto $productos;
    private string $carpetaImagenes;

    public function __construct()
    {
        Autenticacion::exigirAdministrador();
        $this->productos = new Producto();
        $this->carpetaImagenes = __DIR__ . '/../../public/img/productos/';
    }

    /** Guarda un mensaje temporal y vuelve al panel. */
    private function volverAlPanel(string $mensaje): never
    {
        $_SESSION['mensaje_admin'] = $mensaje;
        header('Location: ' . URL_BASE . '/admin');
        exit;
    }

    /** Permite únicamente formularios POST con token válido. */
    private function exigirFormularioSeguro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }
    }

    /** Convierte los campos recibidos en el formato esperado por Producto. */
    private function obtenerDatosFormulario(string $imagen): array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($nombre === '' || $categoria === '' || $descripcion === '') {
            throw new RuntimeException('Completa nombre, categoría y descripción.');
        }

        return [
            'nombre' => $nombre,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'precio' => max(0, (float) ($_POST['precio'] ?? 0)),
            'stock' => max(0, (int) ($_POST['stock'] ?? 0)),
            'imagen' => $imagen,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    /** Valida y almacena una imagen nueva. Si no llega archivo conserva la actual. */
    private function guardarImagen(?string $imagenActual = null): string
    {
        $archivo = $_FILES['imagen'] ?? null;
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $imagenActual ?? '';
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('La imagen debe pesar como máximo 5 MB.');
        }

        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensiones[$tipo])) {
            throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        if (!is_dir($this->carpetaImagenes) && !mkdir($this->carpetaImagenes, 0755, true)) {
            throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
        }

        $nombreArchivo = bin2hex(random_bytes(12)) . '.' . $extensiones[$tipo];
        if (!move_uploaded_file($archivo['tmp_name'], $this->carpetaImagenes . $nombreArchivo)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }

        return 'public/img/productos/' . $nombreArchivo;
    }

    public function crear(): void
    {
        $producto = ['nombre' => '', 'categoria' => '', 'descripcion' => '', 'precio' => '', 'stock' => 0, 'imagen' => '', 'activo' => 1];
        $titulo = 'Agregar producto';
        $accion = URL_BASE . '/producto/guardar';
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function guardar(): void
    {
        $this->exigirFormularioSeguro();

        try {
            $this->productos->crear($this->obtenerDatosFormulario($this->guardarImagen()));
            $this->volverAlPanel('Producto creado correctamente.');
        } catch (Throwable $error) {
            $this->volverAlPanel($error->getMessage());
        }
    }

    public function editar(int $id): void
    {
        $producto = $this->productos->buscar($id);
        if (!$producto) {
            $this->volverAlPanel('Producto no encontrado.');
        }

        $titulo = 'Editar producto';
        $accion = URL_BASE . '/producto/actualizar/' . $id;
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function actualizar(int $id): void
    {
        $this->exigirFormularioSeguro();
        $productoActual = $this->productos->buscar($id);

        if (!$productoActual) {
            $this->volverAlPanel('Producto no encontrado.');
        }

        try {
            $datos = $this->obtenerDatosFormulario($this->guardarImagen($productoActual['imagen']));
            $this->productos->actualizar($id, $datos);
            $this->volverAlPanel('Producto actualizado correctamente.');
        } catch (Throwable $error) {
            $this->volverAlPanel($error->getMessage());
        }
    }
}
