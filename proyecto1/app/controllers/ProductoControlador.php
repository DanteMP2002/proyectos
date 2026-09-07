<?php
require_once __DIR__ . '/../helpers/Autenticacion.php';
require_once __DIR__ . '/../models/Producto.php';

/** Gestiona productos, portada y galería de imágenes. */
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

    private function volverAlPanel(string $mensaje): never
    {
        $_SESSION['mensaje_admin'] = $mensaje;
        header('Location: ' . URL_BASE . '/admin');
        exit;
    }

    private function exigirFormularioSeguro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Autenticacion::validarToken($_POST['token'] ?? null)) {
            http_response_code(403);
            exit('Solicitud no válida.');
        }
    }

    /** Obtiene los campos que pertenecen exclusivamente a la tabla productos. */
    private function obtenerDatosFormulario(): array
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
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    /** Guarda un archivo de imagen y devuelve la ruta pública que va a la base de datos. */
    private function guardarArchivo(array $archivo): string
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new RuntimeException('Cada imagen debe pesar como máximo 5 MB.');
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

    /** La portada es obligatoria al crear y opcional al editar. */
    private function guardarPortada(bool $esObligatoria): ?string
    {
        $archivo = $_FILES['imagen_principal'] ?? null;
        $sinArchivo = !$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;

        if ($sinArchivo && $esObligatoria) {
            throw new RuntimeException('Debes seleccionar una imagen principal para el producto.');
        }

        return $sinArchivo ? null : $this->guardarArchivo($archivo);
    }

    /** Recorre el campo multiple y guarda únicamente archivos seleccionados. */
    private function guardarImagenesAdicionales(): array
    {
        $archivos = $_FILES['imagenes_adicionales'] ?? null;
        if (!$archivos || !is_array($archivos['name'])) {
            return [];
        }

        $rutas = [];
        foreach ($archivos['name'] as $indice => $nombre) {
            if ($nombre === '' || $archivos['error'][$indice] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $rutas[] = $this->guardarArchivo([
                'name' => $nombre,
                'type' => $archivos['type'][$indice],
                'tmp_name' => $archivos['tmp_name'][$indice],
                'error' => $archivos['error'][$indice],
                'size' => $archivos['size'][$indice],
            ]);
        }

        return $rutas;
    }

    public function crear(): void
    {
        $producto = ['nombre' => '', 'categoria' => '', 'descripcion' => '', 'precio' => '', 'stock' => 0, 'activo' => 1];
        $imagenes = [];
        $titulo = 'Agregar producto';
        $accion = URL_BASE . '/producto/guardar';
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function guardar(): void
    {
        $this->exigirFormularioSeguro();

        try {
            $portada = $this->guardarPortada(true);
            $adicionales = $this->guardarImagenesAdicionales();
            $productoId = $this->productos->crear($this->obtenerDatosFormulario());
            $this->productos->guardarImagenes($productoId, $portada, $adicionales);
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

        $imagenes = $this->productos->listarImagenes($id);
        $titulo = 'Editar producto';
        $accion = URL_BASE . '/producto/actualizar/' . $id;
        require __DIR__ . '/../views/formulario_producto.php';
    }

    public function actualizar(int $id): void
    {
        $this->exigirFormularioSeguro();
        if (!$this->productos->buscar($id)) {
            $this->volverAlPanel('Producto no encontrado.');
        }

        try {
            $portada = $this->guardarPortada(false);
            $adicionales = $this->guardarImagenesAdicionales();
            $this->productos->actualizar($id, $this->obtenerDatosFormulario());
            $this->productos->guardarImagenes($id, $portada, $adicionales);
            $this->volverAlPanel('Producto actualizado correctamente.');
        } catch (Throwable $error) {
            $this->volverAlPanel($error->getMessage());
        }
    }
}
