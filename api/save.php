<?php
// ============================================================
// Zentra API – POST /api/save.php
// Called by admin/page-edit.php via fetch()
// Saves section fields + items, then pings webhook
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

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    $body = $_POST; // fallback for form POST
}

$section_id = (int)($body['section_id'] ?? 0);
if ($section_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing section_id']);
    exit;
}

// Security: verify section belongs to current user's project
$stmt = db()->prepare(
    'SELECT s.id, p.project_id FROM sections s
     JOIN pages p ON p.id = s.page_id
     WHERE s.id = ? AND p.project_id = ?'
);
$stmt->execute([$section_id, current_project_id()]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Access denied']);
    exit;
}

$fields = $body['fields'] ?? [];  // array: [field_key => value, ...]

// items_submitted=1 signals that the form intentionally manages items.
// If set and items key is absent (all rows deleted), we clear section_items.
// If not set (form has no repeater), we leave section_items untouched.
$items_submitted = !empty($body['items_submitted']);
$items = isset($body['items']) && is_array($body['items'])
    ? $body['items']
    : ($items_submitted ? [] : null);

$db = db();
$db->beginTransaction();

try {
    // Upsert scalar fields
    $upsert = $db->prepare(
        'INSERT INTO section_fields (section_id, field_key, field_value, field_type)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE field_value = VALUES(field_value), field_type = VALUES(field_type)'
    );
    // field_types can be passed explicitly as fields_type[key] = 'url'|'textarea'|'number'|'bool'|'text'
    $explicit_types = $body['fields_type'] ?? [];
    $allowed_types  = ['text', 'textarea', 'url', 'bool', 'number', 'color', 'select', 'json'];
    foreach ($fields as $key => $val) {
        // Sanitize key
        if (!preg_match('/^[a-z0-9_]+$/i', $key)) continue;
        // Determine field type: explicit hint wins, then default to 'text'
        if (isset($explicit_types[$key]) && in_array($explicit_types[$key], $allowed_types, true)) {
            $type = $explicit_types[$key];
        } else {
            $type = 'text';
        }
        $upsert->execute([$section_id, $key, (string)$val, $type]);
    }

    // Replace items (repeater rows)
    if ($items !== null && is_array($items)) {
        $db->prepare('DELETE FROM section_items WHERE section_id = ?')->execute([$section_id]);
        $ins = $db->prepare('INSERT INTO section_items (section_id, sort_order, item_json) VALUES (?, ?, ?)');
        foreach (array_values($items) as $i => $item) {
            if (!is_array($item)) continue;
            $ins->execute([$section_id, $i, json_encode($item, JSON_UNESCAPED_UNICODE)]);
        }
    }

    // Toggle enabled
    if (isset($body['enabled'])) {
        $db->prepare('UPDATE sections SET enabled = ? WHERE id = ?')
           ->execute([(int)(bool)$body['enabled'], $section_id]);
    }

    $db->commit();

    log_activity(current_project_id(), 'content_saved', 'section', $section_id);
} catch (Throwable $e) {
    $db->rollBack();
    error_log('zentra save error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error']);
    exit;
}

// Non-blocking webhook ping (runs after response sent on PHP-FPM)
if (function_exists('fastcgi_finish_request')) {
    echo json_encode(['ok' => true]);
    fastcgi_finish_request();
    ping_webhook(current_project_id());
} else {
    ping_webhook(current_project_id());
    echo json_encode(['ok' => true]);
}
