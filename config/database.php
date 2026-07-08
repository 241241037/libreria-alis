<?php
/**
 * Conexión a la base de datos.
 * Funciona automáticamente en DOS entornos:
 *   - XAMPP local: usa las constantes de abajo
 *   - Railway (producción): lee las variables de entorno que Railway inyecta
 */

// Railway inyecta estas variables de entorno cuando agregas el plugin MySQL.
// Si no existen (estás en XAMPP), usa los valores locales.
define('DB_HOST',    getenv('MYSQLHOST')     ?: 'localhost');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: 'libreria_alis');
define('DB_USER',    getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: '');
define('DB_PORT',    getenv('MYSQLPORT')     ?: '3306');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST
             . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos. Detalle: ' . $e->getMessage());
        }
    }
    return $pdo;
}
