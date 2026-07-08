<?php
use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias del helper de autenticación.
 * No requieren base de datos.
 */
class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION = []; // sesión limpia entre cada test
    }

    // ── isLoggedIn() es false cuando no hay sesión ──
    public function testNotLoggedInByDefault(): void
    {
        $this->assertFalse(isLoggedIn());
    }

    // ── isLoggedIn() es true cuando existe user_id en sesión ──
    public function testLoggedInWhenSessionSet(): void
    {
        $_SESSION['user_id'] = 1;
        $this->assertTrue(isLoggedIn());
    }

    // ── isAdmin() es false para rol cliente ──
    public function testIsAdminFalseForCliente(): void
    {
        $_SESSION['user_id'] = 2;
        $_SESSION['rol'] = 'cliente';
        $this->assertFalse(isAdmin());
    }

    // ── isAdmin() es true para rol admin ──
    public function testIsAdminTrueForAdmin(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['rol'] = 'admin';
        $this->assertTrue(isAdmin());
    }

    // ── currentUser() devuelve los datos de la sesión activa ──
    public function testCurrentUserReturnsSessionData(): void
    {
        $_SESSION['user_id'] = 42;
        $_SESSION['nombre']  = 'Ana Casique';
        $_SESSION['email']   = 'ana@test.com';
        $_SESSION['rol']     = 'cliente';

        $user = currentUser();
        $this->assertSame(42, $user['id']);
        $this->assertSame('Ana Casique', $user['nombre']);
        $this->assertSame('cliente', $user['rol']);
    }

    // ── currentUser() devuelve valores seguros cuando no hay sesión ──
    public function testCurrentUserDefaultsWhenNoSession(): void
    {
        $user = currentUser();
        $this->assertNull($user['id']);
        $this->assertSame('', $user['nombre']);
        $this->assertSame('cliente', $user['rol']);
    }
}
