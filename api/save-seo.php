<?php
// ============================================================
// Zentra API – POST /api/save-seo.php
// Saves SEO fields for a page
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['ok'=>false]); exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: $_POST;

$page_id = (int)($body['page_id'] ?? 0);
if ($page_id <= 0) {
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing page_id']); exit;
}

// Ownership check
$stmt = db()->prepare('SELECT id FROM pages WHERE id = ? AND project_id = ?');
$stmt->execute([$page_id, current_project_id()]);
if (!$stmt->fetch()) {
    http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Access denied']); exit;
}

$allowed = ['seo_title','seo_description','og_title','og_description','og_image','noindex'];
$sets = []; $params = [];
foreach ($allowed as $col) {
    if (array_key_exists($col, $body)) {
        $sets[]   = "`{$col}` = ?";
        $params[] = $col === 'noindex' ? (int)(bool)$body[$col] : (string)$body[$col];
    }
}
if (empty($sets)) {
    echo json_encode(['ok'=>true]); exit;
}
$params[] = $page_id;
db()->prepare('UPDATE pages SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

log_activity(current_project_id(), 'seo_saved', 'page', $page_id);

ping_webhook(current_project_id());
echo json_encode(['ok' => true]);
