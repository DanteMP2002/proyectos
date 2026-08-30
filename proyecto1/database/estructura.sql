-- Selecciona primero tu base de datos existente en phpMyAdmin y luego importa este archivo.
-- El nombre no se guarda aquí porque se define en el config.php privado del hosting.

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'administrador') NOT NULL DEFAULT 'cliente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    imagen VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    usuario_id INT UNSIGNED NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('yape', 'tarjeta', 'transferencia') NOT NULL,
    estado ENUM('pendiente', 'pagado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE detalle_pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Crea primero una cuenta con la tienda y luego asígnale el rol de administrador:
-- UPDATE usuarios SET rol = 'administrador' WHERE correo = 'tu-correo@ejemplo.com';

INSERT INTO productos (nombre, categoria, descripcion, precio, stock, imagen) VALUES
('Vestido Aurora', 'Vestidos', 'Un diseño elegante para una celebración inolvidable.', 1290.00, 4, 'public/img/logo.jpg'),
('Centro de mesa floral', 'Decoración', 'Detalle delicado para vestir cada mesa.', 95.00, 18, 'public/img/logo.jpg'),
('Anillos Promesa', 'Joyería', 'Símbolo clásico para comenzar una nueva etapa.', 680.00, 7, 'public/img/logo.jpg');
