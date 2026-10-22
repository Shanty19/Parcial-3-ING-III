<?php
/**
 * config.php
 * Configuración de conexión a la base de datos.
 * AJUSTA estos 4 valores según tu servidor / base de datos.
 */

$DB_HOST = 'localhost';
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
// ÚNICO lugar donde se define. Si renombras o mueves la carpeta del
// proyecto en htdocs, cambia SOLO la línea de BUS_URL.
// ------------------------------------------------------------------
if (!defined('BUS_URL')) {
    define('BUS_URL', 'http://localhost/Clinicaprueba1/Bus.php');
}
if (!defined('BUS_URI')) {
    define('BUS_URI', 'urn:busclinica');
}
