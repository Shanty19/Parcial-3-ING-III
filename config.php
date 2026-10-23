<?php
/**
 * config.php
 * Configuración de conexión a la base de datos.
 * AJUSTA estos 4 valores según tu servidor / base de datos.
 */

$DB_HOST = '127.0.0.1';
$DB_NAME = 'clinica_db';   // nombre de tu base de datos
$DB_USER = 'root';         // usuario de MySQL
$DB_PASS = '';             // contraseña de MySQL

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// ------------------------------------------------------------------
// URL del bus SOAP.
// El servidor integrado de PHP solo atiende una petición a la vez en Windows,
// así que el app y el bus deben correr en puertos distintos.
// ------------------------------------------------------------------
if (!defined('BUS_URL')) {
    define('BUS_URL', 'http://localhost/parcial3/Parcial-3-ING-III/Bus.php');
}
if (!defined('BUS_URI')) {
    define('BUS_URI', 'urn:busclinica');
}
