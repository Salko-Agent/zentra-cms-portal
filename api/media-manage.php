<?php
// ============================================================
// Zentra API – POST /api/media-manage.php
// Media: delete, update alt text
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$project_id = current_project_id();
$action     = $_POST['action'] ?? '';
$db         = db();

switch ($action) {

    // ── Delete media ──────────────────────────────────────
    case 'delete':
        $media_id = (int)($_POST['media_id'] ?? 0);
        if ($media_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Missing media_id']);
            exit;
        }

        // Get file info
        $stmt = $db->prepare('SELECT id, filename, url FROM media WHERE id = ? AND project_id = ?');
        $stmt->execute([$media_id, $project_id]);
        $media = $stmt->fetch();
        if (!$media) {
            echo json_encode(['ok' => false, 'error' => 'Media nicht gefunden']);
            exit;
        }

        // Try to delete local file — basename() prevents path-traversal via stored filename
        $local_path = dirname(__DIR__) . '/uploads/' . $project_id . '/' . basename($media['filename']);
        if (file_exists($local_path)) {
            @unlink($local_path);
        }

        $db->prepare('DELETE FROM media WHERE id = ? AND project_id = ?')
           ->execute([$media_id, $project_id]);

        log_activity($project_id, 'media_deleted', 'media', $media_id, $media['filename']);
        echo json_encode(['ok' => true]);
        break;

    // ── Update alt text ──────────────────────────────────
    case 'update_alt':
        $media_id = (int)($_POST['media_id'] ?? 0);
        $alt_text = trim($_POST['alt_text'] ?? '');

        if ($media_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Missing media_id']);
            exit;
        }

        $stmt = $db->prepare('UPDATE media SET alt_text = ? WHERE id = ? AND project_id = ?');
        $stmt->execute([$alt_text, $media_id, $project_id]);

        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
