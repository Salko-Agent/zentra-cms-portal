<?php
// ============================================================
// Zentra API – GET /api/media-list.php
// Returns all media for the current project as JSON
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();

$project_id = current_project_id();

$stmt = db()->prepare(
    'SELECT id, filename, original_name, url, alt_text, created_at
     FROM media WHERE project_id = ? ORDER BY created_at DESC'
);
$stmt->execute([$project_id]);
$media = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['ok' => true, 'media' => $media]);
