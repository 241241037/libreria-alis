<?php
// Bootstrap para las pruebas: carga helpers de sesión e i18n
// sin levantar una conexión real a la BD (se mockea en cada test).
define('TESTING', true);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';
