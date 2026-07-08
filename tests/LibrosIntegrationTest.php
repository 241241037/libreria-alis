<?php
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de integración de api/libros.php.
 *
 * Requieren que MySQL esté corriendo (XAMPP) y que la BD libreria_alis exista.
 * Se ejecutan con: php vendor/bin/phpunit tests/LibrosIntegrationTest.php
 *
 * IMPORTANTE: Usa una fila de prueba temporal con ISBN único 'TEST-ISBN-001'
 * y la elimina al final para no contaminar los datos reales.
 */
class LibrosIntegrationTest extends TestCase
{
    private static \PDO $pdo;
    private static int $testId = 0;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../config/database.php';
        self::$pdo = getConnection();
        // Limpia si quedó basura de una ejecución anterior
        self::$pdo->exec("DELETE FROM libro WHERE isbn = 'TEST-ISBN-001'");
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->exec("DELETE FROM libro WHERE isbn = 'TEST-ISBN-001'");
    }

    // ── INSERT de un libro de prueba ──
    public function testInsertLibro(): void
    {
        $stmt = self::$pdo->prepare(
            "INSERT INTO libro (titulo, autor, genero, isbn, precio, stock, imagen_url)
             VALUES ('Libro de Prueba PHPUnit', 'Autor Test', 'Pruebas', 'TEST-ISBN-001', 99.99, 5, NULL)"
        );
        $result = $stmt->execute();
        self::$testId = (int) self::$pdo->lastInsertId();

        $this->assertTrue($result);
        $this->assertGreaterThan(0, self::$testId);
    }

    // ── SELECT devuelve el libro recién creado ──
    public function testSelectLibroById(): void
    {
        $stmt = self::$pdo->prepare("SELECT * FROM libro WHERE id = ?");
        $stmt->execute([self::$testId]);
        $libro = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($libro, "El libro insertado no se encontró en la BD.");
        $this->assertSame('Libro de Prueba PHPUnit', $libro['titulo']);
        $this->assertSame('TEST-ISBN-001', $libro['isbn']);
        $this->assertEquals(99.99, (float) $libro['precio']);
        $this->assertEquals(5, (int) $libro['stock']);
    }

    // ── Búsqueda por título parcial devuelve resultados ──
    public function testSearchByTitlePartial(): void
    {
        $like = '%Prueba%';
        $stmt = self::$pdo->prepare(
            "SELECT id FROM libro WHERE titulo LIKE :q1 OR autor LIKE :q2 OR genero LIKE :q3 OR isbn LIKE :q4 LIMIT 10"
        );
        $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like]);
        $rows = $stmt->fetchAll();
        $this->assertNotEmpty($rows, "La búsqueda parcial no devolvió resultados.");
    }

    // ── UPDATE modifica el stock correctamente ──
    public function testUpdateStock(): void
    {
        $stmt = self::$pdo->prepare("UPDATE libro SET stock = stock - 1 WHERE id = ?");
        $stmt->execute([self::$testId]);

        $stmt2 = self::$pdo->prepare("SELECT stock FROM libro WHERE id = ?");
        $stmt2->execute([self::$testId]);
        $row = $stmt2->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals(4, (int) $row['stock'], "El stock no se decrementó correctamente.");
    }

    // ── ISBN duplicado lanza excepción PDO ──
    public function testDuplicateIsbnThrows(): void
    {
        $this->expectException(\PDOException::class);
        $stmt = self::$pdo->prepare(
            "INSERT INTO libro (titulo, autor, genero, isbn, precio, stock)
             VALUES ('Duplicado', 'Test', 'Test', 'TEST-ISBN-001', 10, 1)"
        );
        $stmt->execute(); // debe fallar por UNIQUE isbn
    }

    // ── DELETE elimina el registro ──
    public function testDeleteLibro(): void
    {
        $stmt = self::$pdo->prepare("DELETE FROM libro WHERE id = ?");
        $result = $stmt->execute([self::$testId]);
        $this->assertTrue($result);

        $stmt2 = self::$pdo->prepare("SELECT id FROM libro WHERE id = ?");
        $stmt2->execute([self::$testId]);
        $this->assertFalse($stmt2->fetch(), "El libro no fue eliminado.");
    }
}
