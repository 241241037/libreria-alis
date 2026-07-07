<?php
/**
 * /api/google_books.php?q=texto
 * Proxy hacia la API pública de Google Books:
 * https://developers.google.com/books/docs/v1/using
 *
 * Se usa SOLO en el panel de administración, para autocompletar
 * título / autor / género / portada al dar de alta un libro nuevo,
 * sin tener que teclear todo a mano.
 *
 * No requiere API key para búsquedas básicas (cuota limitada por IP,
 * suficiente para uso interno). Si más adelante quieres una cuota más
 * alta, se puede agregar `&key=TU_API_KEY` a la URL de Google.
 */
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/../includes/auth.php';

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
if (!isAdmin()) {
    respond(['error' => 'Solo administradores pueden usar esta búsqueda.'], 403);
}

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    respond([]);
}

$url = 'https://www.googleapis.com/books/v1/volumes?country=US&maxResults=8&q=' . urlencode($q);
$raw = false;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_USERAGENT => 'LibreriaALIS/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
}

if ($raw === false && ini_get('allow_url_fopen')) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 6,
            'header' => "User-Agent: LibreriaALIS/1.0\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
}

if ($raw === false) {
    // Sin internet, API caída, o tiempo de espera agotado.
    respond(['error' => 'No se pudo conectar con Google Books. Verifica tu conexión a internet.'], 502);
}

$data = json_decode($raw, true);
$items = $data['items'] ?? [];

$results = array_map(function ($item) {
    $info = $item['volumeInfo'] ?? [];
    $isbn = '';
    foreach (($info['industryIdentifiers'] ?? []) as $id) {
        if ($id['type'] === 'ISBN_13') { $isbn = $id['identifier']; break; }
        if ($id['type'] === 'ISBN_10' && $isbn === '') { $isbn = $id['identifier']; }
    }
    $cover = $info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? '';
    // Google a veces regresa http:// y enlaces de baja resolución con zoom=1; los normalizamos.
    $cover = str_replace('http://', 'https://', $cover);
    $cover = preg_replace('/&edge=curl/', '', $cover);

    return [
        'titulo' => $info['title'] ?? '',
        'autor' => implode(', ', $info['authors'] ?? []),
        'genero' => $info['categories'][0] ?? '',
        'isbn' => $isbn,
        'imagen_url' => $cover,
        'anio' => isset($info['publishedDate']) ? substr($info['publishedDate'], 0, 4) : '',
    ];
}, $items);

// Descarta resultados sin título (a veces Google regresa entradas incompletas)
$results = array_values(array_filter($results, fn($r) => $r['titulo'] !== ''));

respond($results);
