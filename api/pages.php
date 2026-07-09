<?php
// ============================================================
// Zentra API – POST /api/pages.php
// Page CRUD: create, delete, reorder
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

$db = db();

switch ($action) {

    // ── Create page ────────────────────────────────────────
    case 'create':
        $label = trim($_POST['label'] ?? '');
        $slug  = trim($_POST['slug']  ?? '');

        if ($label === '') {
            echo json_encode(['ok' => false, 'error' => 'Seitenname erforderlich']);
            exit;
        }

        // Auto-generate slug from label if not provided
        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));
            $slug = trim($slug, '-');
        }

        // Sanitize slug
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug));
        if ($slug === '') {
            echo json_encode(['ok' => false, 'error' => 'Ungültiger URL-Slug']);
            exit;
        }

        // Check uniqueness
        $check = $db->prepare('SELECT id FROM pages WHERE project_id = ? AND slug = ?');
        $check->execute([$project_id, $slug]);
        if ($check->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Slug "' . $slug . '" existiert bereits']);
            exit;
        }

        // Get next sort order
        $max = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM pages WHERE project_id = ?');
        $max->execute([$project_id]);
        $sort = (int)$max->fetchColumn();

        $db->beginTransaction();
        try {
            $ins = $db->prepare(
                'INSERT INTO pages (project_id, slug, label, seo_title, seo_description, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([$project_id, $slug, $label, $label, '', $sort]);
            $page_id = (int)$db->lastInsertId();

            // Create default hero section
            $db->prepare(
                'INSERT INTO sections (page_id, section_key, label, section_type, enabled, sort_order)
                 VALUES (?, ?, ?, ?, 1, 0)'
            )->execute([$page_id, 'hero', 'Hero', 'hero']);

            $db->commit();
            log_activity($project_id, 'page_created', 'page', $page_id, $label);

            echo json_encode(['ok' => true, 'page_id' => $page_id, 'slug' => $slug]);
        } catch (Throwable $e) {
            $db->rollBack();
            error_log('page create error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Datenbankfehler']);
        }
        break;

    // ── Delete page ────────────────────────────────────────
    case 'delete':
        $page_id = (int)($_POST['page_id'] ?? 0);
        if ($page_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Missing page_id']);
            exit;
        }

        // Verify ownership
        $check = $db->prepare('SELECT id, slug, label FROM pages WHERE id = ? AND project_id = ?');
        $check->execute([$page_id, $project_id]);
        $page = $check->fetch();
        if (!$page) {
            echo json_encode(['ok' => false, 'error' => 'Seite nicht gefunden']);
            exit;
        }

        // Prevent deleting home page
        if ($page['slug'] === 'home') {
            echo json_encode(['ok' => false, 'error' => 'Die Startseite kann nicht gelöscht werden']);
            exit;
        }

        $db->prepare('DELETE FROM pages WHERE id = ? AND project_id = ?')
           ->execute([$page_id, $project_id]);

        log_activity($project_id, 'page_deleted', 'page', $page_id, $page['label']);
        echo json_encode(['ok' => true]);
        break;

    // ── Reorder pages ──────────────────────────────────────
    case 'reorder':
        $order = $_POST['order'] ?? '';
        if (is_string($order)) {
            $order = json_decode($order, true);
        }
        if (!is_array($order)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid order data']);
            exit;
        }

        $stmt = $db->prepare('UPDATE pages SET sort_order = ? WHERE id = ? AND project_id = ?');
        foreach ($order as $i => $pid) {
            $stmt->execute([(int)$i, (int)$pid, $project_id]);
        }
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
