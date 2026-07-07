<?php
/**
 * Conexión a la base de datos (XAMPP / MySQL)
 * Ajusta estos valores si tu instalación de XAMPP usa
 * un usuario/contraseña distinto al de fábrica (root / sin contraseña).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'libreria_alis');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos. Verifica que XAMPP (Apache + MySQL) esté corriendo y que la base "libreria_alis" exista. Detalle: ' . $e->getMessage());
        }
    }
    return $pdo;
}
