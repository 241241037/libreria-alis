<?php
require_once __DIR__ . '/includes/auth.php';
startAppSession();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
