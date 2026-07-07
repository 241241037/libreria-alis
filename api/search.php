<?php
/**
 * GET /api/search.php?q=texto
 * Devuelve sugerencias de autocompletado (título, autor, ISBN)
 * tomadas directamente de la base de datos.
 *
 * NOTA: usamos ob_start()/ob_clean() para garantizar que NINGÚN warning,
 * notice o deprecation de PHP pueda colarse antes del JSON y romper el
 * fetch() del navegador (causa típica de "Unexpected token '<'").
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

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    respond([]);
}

$pdo = getConnection();
$like = '%' . $q . '%';

$stmt = $pdo->prepare(
    'SELECT id, titulo, autor, genero, isbn
     FROM libro
     WHERE titulo LIKE :q1 OR autor LIKE :q2 OR genero LIKE :q3 OR isbn LIKE :q4
     ORDER BY titulo ASC
     LIMIT 8'
);
$stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like]);
$rows = $stmt->fetchAll();

$suggestions = array_map(function ($r) {
    return [
        'id' => (int) $r['id'],
        'label' => $r['titulo'] . ' — ' . $r['autor'],
        'titulo' => $r['titulo'],
        'autor' => $r['autor'],
        'genero' => $r['genero'],
        'isbn' => $r['isbn'],
    ];
}, $rows);

respond($suggestions);
