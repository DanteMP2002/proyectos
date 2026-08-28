<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Proyecto N°1: Tienda virtual de bodas y eventos.">
    <title>Tienda Bodas & Eventos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="hero">
        <nav class="contenedor barra" aria-label="Navegación principal">
            <a class="marca" href="/">
                <img class="logo" src="logo.jpg" alt="Logo" width="50" height="50">
                Dante Mita Puma<span>.</span>
            </a>
            <div class="menu">
                <a href="#proyectos">Proyectos</a>
                <a href="#contacto">Contacto</a>
            </div>
        </nav>
        <section class="contenedor presentacion">
            <span class="etiqueta">Portafolio personal</span>
            <h1>Proyectos que voy construyendo.</h1>
            <p class="descripcion">Este es mi espacio para reunir cada proyecto, mostrar su avance y acceder rápidamente a sus demostraciones.</p>
            <a class="boton" href="#proyectos">Ver proyectos <span aria-hidden="true">↓</span></a>
        </section>
    </header>

    <main class="contenedor" id="proyectos">
        <div class="encabezado-seccion">
            <h2>Mis proyectos</h2>
            <span class="contador">1 proyecto publicado</span>
        </div>
        <div class="proyectos">
            <a class="proyecto" href="https://dmp-lab.freedev.app/proyecto1/" target="_blank" rel="noopener noreferrer">
                <span class="numero">01 — Publicado</span>
                <h3>Tienda virtual de bodas</h3>
                <p>Una tienda virtual orientada a productos y eventos para bodas.</p>
                <span class="ver-proyecto">Visitar proyecto →</span>
            </a>
            <article class="proyecto proximamente">
                <span class="numero">02 — Próximamente</span>
                <h3>Nuevo proyecto</h3>
                <p>Este espacio está listo para el siguiente proyecto que incorpores al portafolio.</p>
                <span class="ver-proyecto">En desarrollo</span>
            </article>
        </div>
    </main>

    <footer id="contacto">
        <div class="contenedor">
            © <?= date('Y') ?> Dante — Portafolio de proyectos
        </div>
    </footer>
</body>
</html>
