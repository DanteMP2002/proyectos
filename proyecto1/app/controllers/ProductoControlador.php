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

    /** Convierte el nombre del producto en una parte segura y legible del archivo. */
    private function slugArchivo(string $texto): string
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto) ?? '';
        return trim($texto, '-') ?: 'producto';
    }

    /** Convierte una posición en etiqueta A, B, ..., Z, AA, AB. */
    private function etiquetaImagen(int $indice): string
    {
        $etiqueta = '';
        do {
            $etiqueta = chr(65 + ($indice % 26)) . $etiqueta;
            $indice = intdiv($indice, 26) - 1;
        } while ($indice >= 0);
        return $etiqueta;
    }

    /** Guarda un archivo con nombre legible, letra de posición e ID de producto. */
    private function guardarArchivo(array $archivo, int $productoId, int $indiceImagen, string $nombreProducto, bool $permitirSobrescritura = false): string
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            $mensajes = [
                UPLOAD_ERR_INI_SIZE => 'supera el límite de subida configurado en PHP',
                UPLOAD_ERR_FORM_SIZE => 'supera el límite permitido por el formulario',
                UPLOAD_ERR_PARTIAL => 'se subió de forma incompleta',
                UPLOAD_ERR_NO_FILE => 'no contiene un archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'no tiene carpeta temporal disponible',
                UPLOAD_ERR_CANT_WRITE => 'no pudo escribirse en el disco',
            ];
            $nombre = $archivo['name'] ?? 'archivo desconocido';
            throw new RuntimeException('La imagen "' . $nombre . '" ' . ($mensajes[$error] ?? 'produjo un error de subida') . '.');
        }

        if (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
            throw new RuntimeException('La imagen "' . ($archivo['name'] ?? 'archivo') . '" supera el máximo de 5 MB.');
        }

        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensiones[$tipo])) {
            throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        if (!is_dir($this->carpetaImagenes) && !mkdir($this->carpetaImagenes, 0755, true)) {
            throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
        }

        $prefijo = $this->slugArchivo($nombreProducto);
        do {
            $letra = $this->etiquetaImagen($indiceImagen);
            $nombreArchivo = $prefijo . '-' . $letra . '_' . $productoId . '.' . $extensiones[$tipo];
            $indiceImagen++;
        } while (!$permitirSobrescritura && is_file($this->carpetaImagenes . $nombreArchivo));

        if (!move_uploaded_file($archivo['tmp_name'], $this->carpetaImagenes . $nombreArchivo)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }

        return 'public/img/productos/' . $nombreArchivo;
    }

    /** La portada es obligatoria al crear y opcional al editar. */
    private function guardarPortada(bool $esObligatoria, int $productoId, string $nombreProducto): ?string
    {
        $archivo = $_FILES['imagen_principal'] ?? null;
        $sinArchivo = !$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;

        if ($sinArchivo && $esObligatoria) {
            throw new RuntimeException('Debes seleccionar una imagen principal para el producto.');
        }

        return $sinArchivo ? null : $this->guardarArchivo($archivo, $productoId, 0, $nombreProducto);
    }

    /** Recorre el campo multiple y guarda únicamente archivos seleccionados. */
    private function guardarImagenesAdicionales(int $productoId, string $nombreProducto, int $indiceInicial): array
    {
        $archivos = $_FILES['imagenes_adicionales'] ?? null;
        if (!$archivos || !is_array($archivos['name'])) {
            return [];
        }

        $rutas = [];
        foreach ($archivos['name'] as $indice => $nombre) {
            if ($nombre === '' && $archivos['error'][$indice] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $rutas[] = $this->guardarArchivo([
                'name' => $nombre,
                'type' => $archivos['type'][$indice],
                'tmp_name' => $archivos['tmp_name'][$indice],
                'error' => $archivos['error'][$indice],
                'size' => $archivos['size'][$indice],
            ], $productoId, $indiceInicial++, $nombreProducto);
        }

        return $rutas;
    }

    /** Guarda el archivo seleccionado para reemplazar una imagen existente. */
    private function guardarImagenReemplazo(int $productoId, int $indiceImagen, string $nombreProducto): ?string
    {
        $archivo = $_FILES['imagen_reemplazo'] ?? null;
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $this->guardarArchivo($archivo, $productoId, $indiceImagen, $nombreProducto, true);
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
            $datosProducto = $this->obtenerDatosFormulario();
            $productoId = $this->productos->crear($datosProducto);
            $portada = $this->guardarPortada(true, $productoId, $datosProducto['nombre']);
            $adicionales = $this->guardarImagenesAdicionales($productoId, $datosProducto['nombre'], 1);
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
            $imagenesActuales = [];
            $accionImagen = $_POST['accion_imagen'] ?? 'ninguna';
            $imagenId = (int) ($_POST['imagen_id_seleccionada'] ?? $_POST['imagen_id_reemplazar'] ?? 0);
            $archivoReemplazo = $_FILES['imagen_reemplazo'] ?? null;
            $hayReemplazo = $archivoReemplazo && ($archivoReemplazo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if ($hayReemplazo && $accionImagen === 'ninguna') {
                $accionImagen = 'reemplazar';
            }

            $imagenesActuales = $this->productos->listarImagenes($id);
            if ($accionImagen !== 'ninguna') {
                $idsImagenes = array_map('intval', array_column($imagenesActuales, 'id'));
                if ($imagenId < 1 || !in_array($imagenId, $idsImagenes, true)) {
                    throw new RuntimeException('Selecciona una imagen de la galería para aplicar la acción.');
                }
            }

            $datosProducto = $this->obtenerDatosFormulario();
            $indiceReemplazo = 0;
            foreach ($imagenesActuales as $indice => $imagenActual) {
                if ((int) $imagenActual['id'] === $imagenId) {
                    $indiceReemplazo = $indice;
                    break;
                }
            }
            $this->productos->actualizar($id, $datosProducto);

            if ($accionImagen === 'portada' && !$this->productos->establecerPortada($id, $imagenId)) {
                throw new RuntimeException('No se pudo cambiar la portada seleccionada.');
            }

            if ($accionImagen === 'reemplazar') {
                $imagenReemplazo = $this->guardarImagenReemplazo($id, $indiceReemplazo, $datosProducto['nombre']);
                if ($imagenReemplazo === null) {
                    throw new RuntimeException('Selecciona un archivo para reemplazar la imagen.');
                }
                $imagenAnterior = $this->productos->reemplazarImagen($id, $imagenId, $imagenReemplazo);
                if ($imagenAnterior === false) {
                    throw new RuntimeException('La imagen seleccionada no pertenece a este producto.');
                }
                $rutaAnterior = __DIR__ . '/../../' . ltrim($imagenAnterior, '/');
                $rutaNueva = __DIR__ . '/../../' . ltrim($imagenReemplazo, '/');
                if ($rutaAnterior !== $rutaNueva && is_file($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            if ($accionImagen === 'eliminar') {
                $imagenAnterior = $this->productos->eliminarImagen($id, $imagenId);
                if ($imagenAnterior === false) {
                    throw new RuntimeException('No se pudo eliminar la imagen seleccionada.');
                }
                $rutaAnterior = __DIR__ . '/../../' . ltrim($imagenAnterior, '/');
                if (is_file($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            $imagenesDespues = $this->productos->listarImagenes($id);
            $adicionales = $this->guardarImagenesAdicionales($id, $datosProducto['nombre'], count($imagenesDespues));
            $this->productos->guardarImagenes($id, null, $adicionales);
            $this->volverAlPanel('Producto actualizado correctamente.');
        } catch (Throwable $error) {
            $this->volverAlPanel($error->getMessage());
        }
    }
}
