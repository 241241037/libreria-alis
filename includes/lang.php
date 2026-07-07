<?php
/**
 * Helper de internacionalización (i18n).
 * Idiomas soportados: es, en, fr, pt, de (máximo 5, según lo solicitado).
 */

define('SUPPORTED_LANGS', ['es', 'en', 'fr', 'pt', 'de']);
define('LANG_NAMES', [
    'es' => 'Español',
    'en' => 'English',
    'fr' => 'Français',
    'pt' => 'Português',
    'de' => 'Deutsch',
]);

function currentLang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'es';
}

function loadTranslations(): array {
    $lang = currentLang();
    $path = __DIR__ . '/../lang/' . $lang . '.php';
    if (!file_exists($path)) {
        $path = __DIR__ . '/../lang/es.php';
    }
    return require $path;
}

function t(string $key, array $dict): string {
    return $dict[$key] ?? $key;
}
