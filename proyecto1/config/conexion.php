<?php

// config.php vive fuera de este repositorio.
// Debe definir: DB_HOST, DB_NAME1, DB_USER y DB_PASS.
require_once __DIR__ . '/../../config.php';

class Conexion
{
    public static function obtener(): PDO
    {
        static $conexion = null;

        // Si todavía no existe una conexión, la creamos.
        if ($conexion === null) {
            $dsn = 'mysql:host=' . DB_HOST
                . ';dbname=' . DB_NAME1
                . ';charset=utf8mb4';

            $conexion = new PDO($dsn, DB_USER, DB_PASS, [
                // Los errores de PDO se convierten en excepciones.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Los resultados se devuelven como arrays asociativos.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Usa prepared statements reales.
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $conexion;
    }
}