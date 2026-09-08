SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_42766503_proyecto1`
--

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `usuarios`
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `rol` enum('cliente','administrador') NOT NULL DEFAULT 'cliente',
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `productos`
-- --------------------------------------------------------
CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `categoria` varchar(80) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `imagenes_producto`
-- --------------------------------------------------------
CREATE TABLE `imagenes_producto` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `es_principal` tinyint(1) NULL DEFAULT NULL, -- NULL = Secundaria, 1 = Principal (Portada)
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `pedidos`
-- --------------------------------------------------------
CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('yape','tarjeta','transferencia') NOT NULL,
  `estado` enum('pendiente','pagado','cancelado') NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `detalle_pedidos`
-- --------------------------------------------------------
CREATE TABLE `detalle_pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `registros_admin`
-- --------------------------------------------------------
CREATE TABLE `registros_admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `accion` varchar(120) NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- --------------------------------------------------------
-- Volcado de datos iniciales obligatorios
-- --------------------------------------------------------
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `clave`, `rol`, `creado_en`) VALUES
(1, 'Administrador', 'admin@test.com', '$2y$10$HsGTEy9LC5zBnhcs0FsBI.mCLjv5.e4/0UkbE54bsiCLsFctA.hjS', 'administrador', '2026-08-30 23:38:20'),
(6, 'Dante Mita', '1688575@senati.pe', '$2y$10$kVQD6kLrsptu16626RtBxO6JETmbnj2R/tj5QaaFTX5LVIb6zGxdi', 'cliente', '2026-09-04 13:56:58');


-- --------------------------------------------------------
-- Índices para tablas volcadas (Llaves Primarias y Únicas)
-- --------------------------------------------------------
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `imagenes_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_producto_principal` (`producto_id`, `es_principal`), -- Regla: Solo un '1' por producto
  ADD KEY `fk_imagenes_producto` (`producto_id`);

ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_pedido_usuario` (`usuario_id`);

ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_pedido` (`pedido_id`),
  ADD KEY `fk_detalle_producto` (`producto_id`);

ALTER TABLE `registros_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_registro_admin_usuario` (`usuario_id`);


-- --------------------------------------------------------
-- AUTO_INCREMENT de las tablas volcadas
-- --------------------------------------------------------
ALTER TABLE `usuarios` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `productos` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `imagenes_producto` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `pedidos` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `detalle_pedidos` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `registros_admin` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- Crea primero una cuenta con la tienda y luego asígnale el rol de administrador:
-- UPDATE usuarios SET rol = 'administrador' WHERE correo = 'tu-correo@ejemplo.com';
-- arregla

INSERT INTO productos (nombre, categoria, descripcion, precio, stock, imagen) VALUES
('Vestido Aurora', 'Vestidos', 'Un diseño elegante para una celebración inolvidable.', 1290.00, 4, 'public/img/logo.jpg'),
('Centro de mesa floral', 'Decoración', 'Detalle delicado para vestir cada mesa.', 95.00, 18, 'public/img/logo.jpg'),
('Anillos Promesa', 'Joyería', 'Símbolo clásico para comenzar una nueva etapa.', 680.00, 7, 'public/img/logo.jpg');


-- Ejecutar una sola vez en la base de datos remota.
-- Las imágenes adicionales usan NULL; solo la portada usa 1.
ALTER TABLE imagenes_producto
  DROP INDEX uk_producto_principal,
  MODIFY es_principal TINYINT(1) NULL DEFAULT NULL;

UPDATE imagenes_producto
SET es_principal = NULL
WHERE es_principal = 0;

ALTER TABLE imagenes_producto
  ADD UNIQUE KEY uk_producto_principal (producto_id, es_principal);
