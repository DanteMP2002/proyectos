<?php
// Punto único de entrada: toda URL pasa primero por este archivo.
session_start();

// La URL base se calcula desde la carpeta actual; no necesita guardarse en Git.
$directorioPublico = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/proyecto1/index.php'));
define('URL_BASE', $directorioPublico === '/' ? '' : rtrim($directorioPublico, '/'));

// Convierte "carrito/agregar/3" en controlador, acción y parámetro.
$ruta = trim($_GET['ruta'] ?? 'inicio', '/');
$partes = $ruta === '' ? ['inicio'] : explode('/', filter_var($ruta, FILTER_SANITIZE_URL));
$nombreControlador = ucfirst(strtolower($partes[0])) . 'Controlador';
$archivoControlador = __DIR__ . '/app/controllers/' . $nombreControlador . '.php';
$accion = $partes[1] ?? 'index';
$parametro = $partes[2] ?? null;

if (!is_file($archivoControlador)) {
    http_response_code(404);
    exit('Página no encontrada.');
}

require_once $archivoControlador;
$controlador = new $nombreControlador();

if (!method_exists($controlador, $accion)) {
    http_response_code(404);
    exit('Acción no encontrada.');
}

$parametro === null ? $controlador->$accion() : $controlador->$accion($parametro);
