<?php
/**
 * setup_admin.php
 * Ejecuta este archivo UNA SOLA VEZ desde el navegador para crear
 * el primer usuario administrador. Después bórralo o renómbralo.
 */
require_once __DIR__ . '/config/database.php';

$mensaje = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || strlen($password) < 6) {
        $mensaje = 'Completa todos los campos. La contraseña debe tener al menos 6 caracteres.';
    } else {
        $pdo = getConnection();
        $check = $pdo->prepare('SELECT id FROM usuario WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $mensaje = 'Ya existe un usuario con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO usuario (nombre, email, password_hash, rol) VALUES (?, ?, ?, "admin")');
            $stmt->execute([$nombre, $email, $hash]);
            $mensaje = 'Administrador creado correctamente. Ya puedes iniciar sesión y, por seguridad, eliminar este archivo (setup_admin.php).';
            $exito = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configuración inicial - Librería ALIS</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-theme="light" data-accent="indigo">
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="brand">Librería ALIS</h1>
    <p class="muted">Configuración inicial · Crear administrador</p>
    <?php if ($mensaje): ?>
      <div class="alert <?= $exito ? 'alert-ok' : 'alert-error' ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if (!$exito): ?>
    <form method="post" class="form-stack">
      <label>Nombre completo
        <input type="text" name="nombre" required>
      </label>
      <label>Correo
        <input type="email" name="email" required>
      </label>
      <label>Contraseña
        <input type="password" name="password" required minlength="6">
      </label>
      <button type="submit" class="btn btn-primary">Crear administrador</button>
    </form>
    <?php else: ?>
      <a class="btn btn-primary" href="login.php">Ir a iniciar sesión</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
