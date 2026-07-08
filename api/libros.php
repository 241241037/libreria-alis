<?php
/**
 * /api/libros.php
 * GET    -> lista / búsqueda de libros (cualquier usuario logueado)
 * POST   -> crear libro (solo admin)
 * PUT    -> actualizar libro (solo admin)   body JSON: { id, ...campos }
 * DELETE -> eliminar libro (solo admin)     body JSON: { id }
 *
 * NOTA: ob_start()/ob_clean() evita que cualquier warning/notice de PHP
 * rompa el JSON de salida (causa típica de "Unexpected token '<'" en el navegador).
 */
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

startAppSession();

function respond($data, int $code = 200): void {
    ob_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if (!isLoggedIn()) {
    respond(['error' => 'No autenticado'], 401);
}

$pdo = getConnection();
$method = $_SERVER['REQUEST_METHOD'];

function bodyJson(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function validateBook(array $d): ?string {
    if (trim($d['titulo'] ?? '') === '' || trim($d['isbn'] ?? '') === '') {
        return 'required_fields';
    }
    return null;
}

switch ($method) {
    case 'GET':
        $q = trim($_GET['q'] ?? '');
        $genero = trim($_GET['genero'] ?? '');

        // Búsqueda: coincidencia desde el INICIO de la palabra (titulo LIKE 'El%')
        // Para autor/género/ISBN se mantiene búsqueda parcial con %q%
        $conditions = [];
        $params = [];

        if ($q !== '') {
            $likeStart = $q . '%';   // desde el inicio (título)
            $likeAny   = '%' . $q . '%'; // en cualquier posición (resto)
            $conditions[] = '(titulo LIKE :q1 OR autor LIKE :q2 OR genero LIKE :q3 OR isbn LIKE :q4)';
            $params['q1'] = $likeStart;
            $params['q2'] = $likeAny;
            $params['q3'] = $likeAny;
            $params['q4'] = $likeAny;
        }

        if ($genero !== '') {
            $conditions[] = 'genero = :genero';
            $params['genero'] = $genero;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $pdo->prepare("SELECT * FROM libro $where ORDER BY titulo ASC");
        $stmt->execute($params);
        respond($stmt->fetchAll());
        break;

    case 'POST':
        if (!isAdmin()) {
            respond(['error' => 'Solo administradores pueden agregar libros.'], 403);
        }
        $d = bodyJson();
        $err = validateBook($d);
        if ($err) {
            respond(['error' => $err], 422);
        }
        $stmt = $pdo->prepare(
            'INSERT INTO libro (titulo, autor, genero, isbn, precio, stock, imagen_url)
             VALUES (:titulo, :autor, :genero, :isbn, :precio, :stock, :imagen_url)'
        );
        try {
            $stmt->execute([
                'titulo' => $d['titulo'],
                'autor' => $d['autor'] ?? '',
                'genero' => $d['genero'] ?? '',
                'isbn' => $d['isbn'],
                'precio' => (float) ($d['precio'] ?? 0),
                'stock' => (int) ($d['stock'] ?? 0),
                'imagen_url' => trim($d['imagen_url'] ?? '') !== '' ? trim($d['imagen_url']) : null,
            ]);
            respond(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            respond(['error' => 'El ISBN ya existe o los datos son inválidos.'], 409);
        }
        break;

    case 'PUT':
        if (!isAdmin()) {
            respond(['error' => 'Solo administradores pueden editar libros.'], 403);
        }
        $d = bodyJson();
        if (empty($d['id'])) {
            respond(['error' => 'Falta el id del libro.'], 422);
        }
        $err = validateBook($d);
        if ($err) {
            respond(['error' => $err], 422);
        }
        $stmt = $pdo->prepare(
            'UPDATE libro SET titulo=:titulo, autor=:autor, genero=:genero, isbn=:isbn,
             precio=:precio, stock=:stock, imagen_url=:imagen_url WHERE id=:id'
        );
        try {
            $stmt->execute([
                'titulo' => $d['titulo'],
                'autor' => $d['autor'] ?? '',
                'genero' => $d['genero'] ?? '',
                'isbn' => $d['isbn'],
                'precio' => (float) ($d['precio'] ?? 0),
                'stock' => (int) ($d['stock'] ?? 0),
                'imagen_url' => trim($d['imagen_url'] ?? '') !== '' ? trim($d['imagen_url']) : null,
                'id' => (int) $d['id'],
            ]);
            respond(['ok' => true]);
        } catch (PDOException $e) {
            respond(['error' => 'El ISBN ya existe o los datos son inválidos.'], 409);
        }
        break;

    case 'DELETE':
        if (!isAdmin()) {
            respond(['error' => 'Solo administradores pueden eliminar libros.'], 403);
        }
        $d = bodyJson();
        if (empty($d['id'])) {
            respond(['error' => 'Falta el id del libro.'], 422);
        }
        $stmt = $pdo->prepare('DELETE FROM libro WHERE id = ?');
        $stmt->execute([(int) $d['id']]);
        respond(['ok' => true]);
        break;

    default:
        respond(['error' => 'Método no permitido'], 405);
}
