<?php
// api/request-indexing.php — Submit a page URL to Google Indexing API
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/google-auth.php';

header('Content-Type: application/json');

auth_start();
auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!csrf_verify($token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$slug       = trim($_POST['slug'] ?? '');
$project_id = (int)$_SESSION['project_id'];

if (!$slug) {
    echo json_encode(['ok' => false, 'error' => 'Kein Seiten-Slug angegeben']);
    exit;
}

// Get project domain
$db   = db();
$stmt = $db->prepare('SELECT domain FROM projects WHERE id = ?');
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project || empty($project['domain'])) {
    echo json_encode(['ok' => false, 'error' => 'Keine Domain für dieses Projekt konfiguriert. Bitte in den Einstellungen eintragen.']);
    exit;
}

$domain = rtrim($project['domain'], '/');
// Build the full URL: home page is just the domain, others get /slug
$url = ($slug === 'home') ? $domain . '/' : $domain . '/' . ltrim($slug, '/');

// Call Google Indexing API
$scope    = 'https://www.googleapis.com/auth/indexing';
$endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

try {
    $result = google_api_post($endpoint, [
        'url'  => $url,
        'type' => 'URL_UPDATED',
    ], $scope);

    if (!empty($result['error'])) {
        $errBody = is_array($result['body']) ? json_encode($result['body']) : ($result['body'] ?? '');
        error_log("Indexing API error for $url: " . $errBody);
        echo json_encode(['ok' => false, 'error' => 'Google API Fehler (HTTP ' . ($result['http_code'] ?? '?') . '). Stelle sicher, dass der Service Account in der Google Search Console verifiziert ist.']);
        exit;
    }

    echo json_encode([
        'ok'  => true,
        'url' => $url,
        'msg' => "URL erfolgreich zur Indexierung eingereicht: $url",
    ]);

} catch (RuntimeException $e) {
    error_log('request-indexing.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
