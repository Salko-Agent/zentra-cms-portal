<?php
// ============================================================
// Zentra API – POST /api/sync.php
// Manually triggers the webhook for the current project.
// Use after manual DB changes to push content.json to site.
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF: accept token from JSON body, POST field, or header
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];
$token = $body['csrf_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals(csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF token mismatch']);
    exit;
}

$project_id = current_project_id();

// Check webhook_url is set
$stmt = db()->prepare('SELECT webhook_url FROM projects WHERE id = ?');
$stmt->execute([$project_id]);
$proj = $stmt->fetch();

if (empty($proj['webhook_url'])) {
    echo json_encode(['ok' => false, 'error' => 'Keine Webhook-URL konfiguriert']);
    exit;
}

ping_webhook($project_id);

log_activity($project_id, 'manual_sync', 'project', $project_id);

echo json_encode(['ok' => true]);
