<?php

require_once __DIR__ . '/../../config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME1 . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // lanza errores como excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statements reales
        ]
    );
} catch (PDOException $e) {
    // Nunca muestres el error real al usuario en producción
    die('Error de conexión');
}