<?php
require_once __DIR__ . '/includes/auth.php';
startAppSession();
header('Location: ' . (isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;
