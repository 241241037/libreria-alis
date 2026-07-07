<?php
/**
 * /api/reservas.php
 * POST -> crea una reserva (apartado de 48h) a partir del carrito del cliente.
 *         body JSON: { items: [{ libro_id, cantidad }, ...] }
 * Descuenta stock dentro de una transacción y valida existencias.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Método no permitido'], 405);
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$items = is_array($body['items'] ?? null) ? $body['items'] : [];

if (empty($items)) {
    respond(['error' => 'El carrito está vacío.'], 422);
}

$pdo = getConnection();
$user = currentUser();

try {
    $pdo->beginTransaction();

    // Verifica existencias con bloqueo de fila para evitar sobreventas simultáneas (R-01 de la matriz de riesgos)
    $checkStmt = $pdo->prepare('SELECT id, titulo, stock FROM libro WHERE id = ? FOR UPDATE');
    $libros = [];
    foreach ($items as $item) {
        $libroId = (int) ($item['libro_id'] ?? 0);
        $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
        if ($libroId <= 0) continue;

        $checkStmt->execute([$libroId]);
        $libro = $checkStmt->fetch();
        if (!$libro) {
            throw new RuntimeException('Uno de los libros ya no existe.');
        }
        if ($libro['stock'] < $cantidad) {
            throw new RuntimeException('No hay suficientes existencias de "' . $libro['titulo'] . '".');
        }
        $libros[] = ['id' => $libroId, 'cantidad' => $cantidad];
    }

    if (empty($libros)) {
        throw new RuntimeException('El carrito está vacío.');
    }

    $fechaExpiracion = (new DateTime('+48 hours'))->format('Y-m-d H:i:s');

    $reservaStmt = $pdo->prepare(
        'INSERT INTO reserva (usuario_id, fecha_expiracion, estado) VALUES (?, ?, "RESERVADO_PENDIENTE")'
    );
    $reservaStmt->execute([$user['id'], $fechaExpiracion]);
    $reservaId = (int) $pdo->lastInsertId();

    $detalleStmt = $pdo->prepare(
        'INSERT INTO detalle_reserva (reserva_id, libro_id, cantidad) VALUES (?, ?, ?)'
    );
    $stockStmt = $pdo->prepare('UPDATE libro SET stock = stock - ? WHERE id = ?');

    foreach ($libros as $l) {
        $detalleStmt->execute([$reservaId, $l['id'], $l['cantidad']]);
        $stockStmt->execute([$l['cantidad'], $l['id']]);
    }

    $pdo->commit();

    respond([
        'ok' => true,
        'reserva_id' => $reservaId,
        'fecha_expiracion' => $fechaExpiracion,
    ]);
} catch (RuntimeException $e) {
    $pdo->rollBack();
    respond(['error' => $e->getMessage()], 422);
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Error al procesar la reserva.'], 500);
}
