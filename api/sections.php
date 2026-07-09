<?php
// ============================================================
// Zentra API – POST /api/sections.php
// Section CRUD: create, delete, reorder
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

    // ── Create section ───────────────────────────────────────
    case 'create':
        $page_id      = (int)($_POST['page_id'] ?? 0);
        $section_type = trim($_POST['section_type'] ?? '');
        $label        = trim($_POST['label'] ?? '');
        $section_key  = trim($_POST['section_key'] ?? '');

        if ($page_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Fehlende page_id']);
            exit;
        }
        if ($label === '') {
            echo json_encode(['ok' => false, 'error' => 'Bezeichnung erforderlich']);
            exit;
        }

        // Validate section_type
        $valid_types = get_available_section_types();
        if (!isset($valid_types[$section_type])) {
            echo json_encode(['ok' => false, 'error' => 'Sektionstyp ungültig']);
            exit;
        }

        // Verify page belongs to this project
        $check = $db->prepare('SELECT id FROM pages WHERE id = ? AND project_id = ?');
        $check->execute([$page_id, $project_id]);
        if (!$check->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Seite nicht gefunden']);
            exit;
        }

        // Auto-generate section_key from label if not provided
        if ($section_key === '') {
            $section_key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label));
            $section_key = trim($section_key, '_');
        }

        // Sanitize
        $section_key = strtolower(preg_replace('/[^a-z0-9_]/', '', $section_key));
        if ($section_key === '') {
            $section_key = $section_type;
        }

        // Ensure uniqueness within the page (append _2, _3... if needed)
        $base_key = $section_key;
        $suffix   = 1;
        while (true) {
            $dup = $db->prepare('SELECT id FROM sections WHERE page_id = ? AND section_key = ?');
            $dup->execute([$page_id, $section_key]);
            if (!$dup->fetch()) break;
            $suffix++;
            $section_key = $base_key . '_' . $suffix;
        }

        // Next sort_order
        $max = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM sections WHERE page_id = ?');
        $max->execute([$page_id]);
        $sort = (int)$max->fetchColumn();

        try {
            $db->prepare(
                'INSERT INTO sections (page_id, section_key, label, section_type, enabled, sort_order, created_at)
                 VALUES (?, ?, ?, ?, 1, ?, NOW())'
            )->execute([$page_id, $section_key, $label, $section_type, $sort]);
            $section_id = (int)$db->lastInsertId();

            log_activity($project_id, 'section_created', 'section', $section_id, $label);
            ping_webhook($project_id);

            echo json_encode(['ok' => true, 'section_id' => $section_id, 'section_key' => $section_key]);
        } catch (Throwable $e) {
            error_log('section create error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Datenbankfehler']);
        }
        break;

    // ── Delete section ───────────────────────────────────────
    case 'delete':
        $section_id = (int)($_POST['section_id'] ?? 0);
        if ($section_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Fehlende section_id']);
            exit;
        }

        // Verify ownership
        $check = $db->prepare(
            'SELECT s.id, s.label, s.page_id FROM sections s
             JOIN pages p ON p.id = s.page_id
             WHERE s.id = ? AND p.project_id = ?'
        );
        $check->execute([$section_id, $project_id]);
        $sec = $check->fetch();
        if (!$sec) {
            echo json_encode(['ok' => false, 'error' => 'Sektion nicht gefunden']);
            exit;
        }

        // Prevent deleting the last section on a page
        $cnt = $db->prepare('SELECT COUNT(*) FROM sections WHERE page_id = ?');
        $cnt->execute([$sec['page_id']]);
        if ((int)$cnt->fetchColumn() <= 1) {
            echo json_encode(['ok' => false, 'error' => 'Mindestens eine Sektion muss vorhanden sein']);
            exit;
        }

        // CASCADE deletes section_fields + section_items
        $db->prepare('DELETE FROM sections WHERE id = ?')->execute([$section_id]);

        log_activity($project_id, 'section_deleted', 'section', $section_id, $sec['label']);
        ping_webhook($project_id);

        echo json_encode(['ok' => true]);
        break;

    // ── Reorder sections ─────────────────────────────────────
    case 'reorder':
        $page_id = (int)($_POST['page_id'] ?? 0);
        $order   = $_POST['order'] ?? '';

        if ($page_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Fehlende page_id']);
            exit;
        }

        // Verify page belongs to this project
        $check = $db->prepare('SELECT id FROM pages WHERE id = ? AND project_id = ?');
        $check->execute([$page_id, $project_id]);
        if (!$check->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Seite nicht gefunden']);
            exit;
        }

        if (is_string($order)) {
            $order = json_decode($order, true);
        }
        if (!is_array($order)) {
            echo json_encode(['ok' => false, 'error' => 'Ungültige Sortierung']);
            exit;
        }

        $stmt = $db->prepare('UPDATE sections SET sort_order = ? WHERE id = ? AND page_id = ?');
        foreach ($order as $i => $sid) {
            $stmt->execute([(int)$i, (int)$sid, $page_id]);
        }

        ping_webhook($project_id);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
