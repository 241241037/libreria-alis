<?php
use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias del helper de internacionalización (i18n).
 * No requieren base de datos.
 */
class LangTest extends TestCase
{
    // ── t() devuelve el texto correcto para la clave dada ──
    public function testTranslationKeyFound(): void
    {
        $dict = ['app_name' => 'Librería ALIS', 'login_btn' => 'Entrar'];
        $this->assertSame('Librería ALIS', t('app_name', $dict));
        $this->assertSame('Entrar', t('login_btn', $dict));
    }

    // ── t() devuelve la clave misma cuando no existe traducción ──
    public function testTranslationKeyMissing(): void
    {
        $dict = ['app_name' => 'Librería ALIS'];
        $this->assertSame('key_inexistente', t('key_inexistente', $dict));
    }

    // ── Los 5 archivos de idioma existen y son arrays ──
    public function testAllLangFilesLoad(): void
    {
        foreach (['es', 'en', 'fr', 'pt', 'de'] as $lang) {
            $path = __DIR__ . '/../lang/' . $lang . '.php';
            $this->assertFileExists($path, "Falta lang/{$lang}.php");
            $data = require $path;
            $this->assertIsArray($data, "lang/{$lang}.php no devuelve un array");
            $this->assertArrayHasKey('app_name', $data, "lang/{$lang}.php no tiene clave 'app_name'");
        }
    }

    // ── El idioma por defecto es 'es' cuando no hay sesión ──
    public function testDefaultLangIsEs(): void
    {
        // Sin sesión activa, currentLang() debe devolver 'es'
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        unset($_SESSION['lang']);
        unset($_GET['lang']);
        $this->assertSame('es', currentLang());
    }

    // ── currentLang() acepta idiomas soportados y rechaza otros ──
    public function testLangSwitchValid(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_GET['lang'] = 'fr';
        $this->assertSame('fr', currentLang());
    }

    public function testLangSwitchInvalid(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['lang'] = 'es';
        $_GET['lang'] = 'zh'; // no soportado
        $this->assertSame('es', currentLang()); // mantiene el anterior
    }
}
