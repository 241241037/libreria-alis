<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/config/database.php';

startAppSession();
$t = loadTranslations();
$lang = currentLang();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nombre === '' || $email === '' || strlen($password) < 6) {
        $error = t('register_error_fields', $t);
    } elseif ($password !== $password2) {
        $error = t('register_error_match', $t);
    } else {
        $pdo = getConnection();
        $check = $pdo->prepare('SELECT id FROM usuario WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = t('register_error_exists', $t);
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO usuario (nombre, email, password_hash, rol) VALUES (?, ?, ?, "cliente")');
            $stmt->execute([$nombre, $email, $hash]);

            $newId = (int) $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newId;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = 'cliente';
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(t('create_account', $t)) ?> · <?= htmlspecialchars(t('app_name', $t)) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-theme="light" data-accent="indigo">

<div class="auth-top-controls">
  <div class="accent-picker" title="<?= htmlspecialchars(t('accent_color', $t)) ?>">
    <span class="accent-dot" data-accent="indigo" onclick="ALIS.setAccent('indigo')"></span>
    <span class="accent-dot" data-accent="emerald" onclick="ALIS.setAccent('emerald')"></span>
    <span class="accent-dot" data-accent="amber" onclick="ALIS.setAccent('amber')"></span>
    <span class="accent-dot" data-accent="rose" onclick="ALIS.setAccent('rose')"></span>
    <span class="accent-dot" data-accent="slate" onclick="ALIS.setAccent('slate')"></span>
  </div>
  <button class="icon-btn" id="themeToggle" title="<?= htmlspecialchars(t('theme', $t)) ?>" onclick="ALIS.toggleTheme()"></button>
  <select id="langSelectAuth" aria-label="<?= htmlspecialchars(t('language', $t)) ?>">
    <?php foreach (LANG_NAMES as $code => $name): ?>
      <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= $name ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand-mark" aria-hidden="true">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
        <rect x="4" y="6" width="9" height="28" rx="1.5" fill="currentColor" opacity="0.55"/>
        <rect x="15.5" y="3" width="9" height="31" rx="1.5" fill="currentColor" opacity="0.8"/>
        <rect x="27" y="8" width="9" height="26" rx="1.5" fill="currentColor"/>
      </svg>
    </div>
    <h1 class="brand"><?= htmlspecialchars(t('app_name', $t)) ?></h1>
    <p class="muted"><?= htmlspecialchars(t('register_subtitle', $t)) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-stack">
      <label><?= htmlspecialchars(t('full_name', $t)) ?>
        <input type="text" name="nombre" required autofocus value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
      </label>
      <label><?= htmlspecialchars(t('email', $t)) ?>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </label>
      <label><?= htmlspecialchars(t('password', $t)) ?>
        <input type="password" name="password" required minlength="6">
      </label>
      <label><?= htmlspecialchars(t('confirm_password', $t)) ?>
        <input type="password" name="password2" required minlength="6">
      </label>
      <button type="submit" class="btn btn-primary btn-block"><?= htmlspecialchars(t('register_btn', $t)) ?></button>
    </form>

    <p class="hint"><?= htmlspecialchars(t('already_have_account', $t)) ?> <a href="login.php"><?= htmlspecialchars(t('login_link', $t)) ?></a></p>
  </div>
</div>

<script src="assets/js/app.js"></script>
<script>
  document.getElementById('langSelectAuth').addEventListener('change', function () {
    window.location.href = '?lang=' + this.value;
  });
  ALIS.initTheme();
</script>
</body>
</html>
