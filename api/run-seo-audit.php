<?php
// ============================================================
// Zentra API – POST /api/run-seo-audit.php
// Triggers an on-page SEO audit for a URL
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics-fetchers.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();
csrf_verify();

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: $_POST;

$url = trim($body['url'] ?? '');
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige URL']);
    exit;
}

$project_id = current_project_id();

try {
    $result = run_seo_audit($project_id, $url);
    if ($result === null) {
        echo json_encode(['ok' => false, 'error' => 'URL konnte nicht geprüft werden.']);
    } else {
        echo json_encode(['ok' => true, 'score' => $result['score'], 'issues_count' => count($result['issues'])]);
    }
} catch (Throwable $e) {
    error_log('SEO audit error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Audit fehlgeschlagen. Bitte versuche es erneut.']);
}
