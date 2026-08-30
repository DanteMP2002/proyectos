<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda de Bodas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Cabecera -->
    <header>
        <div class="logo">
            <h1>Tienda de Bodas</h1>
        </div>
        <nav>
            <ul>
                <li><a href="#">Inicio</a></li>
                <li><a href="#">Vestidos</a></li>
                <li><a href="#">Decoración</a></li>
                <li><a href="#">Regalos</a></li>
                <li><a href="#">Contacto</a></li>
            </ul>
        </nav>
    </header>

    <!-- Banner principal -->
    <section class="banner">
        <h2>Todo para tu boda soñada</h2>
        <p>Encuentra vestidos, decoración y más.</p>
        <a href="#" class="btn">Ver productos</a>
    </section>

    <!-- Sección de productos -->
    <main>
        <h2>Productos destacados</h2>
        <div class="productos">
            <?php
            // Ejemplo de productos en un array
            $productos = [
                ["nombre" => "Vestido de novia", "precio" => 1200, "imagen" => "vestido.jpg"],
                ["nombre" => "Centro de mesa", "precio" => 80, "imagen" => "centro.jpg"],
                ["nombre" => "Anillos de boda", "precio" => 500, "imagen" => "anillos.jpg"],
            ];

            foreach ($productos as $p) {
                echo "<div class='producto'>";
                echo "<img src='images/".$p['imagen']."' alt='".$p['nombre']."'>";
                echo "<h3>".$p['nombre']."</h3>";
                echo "<p>S/ ".$p['precio']."</p>";
                echo "<a href='#' class='btn'>Comprar</a>";
                echo "</div>";
            }
            ?>
        </div>
    </main>

    <!-- Pie de página -->
    <footer>
        <p>&copy; 2026 Tienda de Bodas. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
