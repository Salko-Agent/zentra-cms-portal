<?php
// ============================================================
// Zentra API – POST /api/submissions-manage.php
// Submissions: delete, mark read, export CSV
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

auth_check();

$project_id = current_project_id();
$action     = $_GET['action'] ?? $_POST['action'] ?? '';
$db         = db();

// ── CSV Export (GET, requires CSRF token) ──────────────────
if ($action === 'export') {
    $csrf = $_GET['token'] ?? '';
    if (!$csrf || !hash_equals(csrf_token(), $csrf)) {
        http_response_code(403);
        exit('CSRF token mismatch');
    }
    $stmt = $db->prepare('SELECT * FROM form_submissions WHERE project_id = ? ORDER BY created_at DESC');
    $stmt->execute([$project_id]);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="submissions_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
    fputcsv($out, ['Datum', 'Formular', 'Vorname', 'Nachname', 'E-Mail', 'Telefon', 'Ziel', 'Nachricht'], ';');

    foreach ($rows as $r) {
        $d = json_decode($r['data_json'], true) ?: [];
        fputcsv($out, [
            $r['created_at'],
            $r['form_type'] ?? $r['page_slug'],
            $d['firstname'] ?? '',
            $d['lastname']  ?? '',
            $d['email']     ?? '',
            $d['phone']     ?? '',
            $d['goal']      ?? $d['interest'] ?? '',
            $d['message']   ?? '',
        ], ';');
    }
    fclose($out);
    exit;
}

// ── POST actions ──────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

switch ($action) {

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM form_submissions WHERE id = ? AND project_id = ?')
           ->execute([$id, $project_id]);
        echo json_encode(['ok' => true]);
        break;

    case 'mark_read':
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('UPDATE form_submissions SET is_read = 1 WHERE id = ? AND project_id = ?')
           ->execute([$id, $project_id]);
        echo json_encode(['ok' => true]);
        break;

    case 'mark_all_read':
        $db->prepare('UPDATE form_submissions SET is_read = 1 WHERE project_id = ?')
           ->execute([$project_id]);
        echo json_encode(['ok' => true]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
