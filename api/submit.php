<?php
// api/submit.php — receive form submissions from client sites
// POST {project_key, api_key, form_type, data_json}
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Fall back to form-encoded POST
    $input = $_POST;
}

$project_key = trim($input['project_key'] ?? '');
$api_key     = trim($input['api_key']     ?? '');
$form_type   = trim($input['form_type']   ?? 'contact');
$data_raw    = $input['data_json']        ?? $input['data'] ?? [];

// Validate required fields
if (!$project_key || !$api_key) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing credentials']);
    exit;
}

// Validate project + API key
$pdo  = db();
$stmt = $pdo->prepare(
    'SELECT id FROM projects WHERE project_key = :pk AND api_key = :ak AND active = 1 LIMIT 1'
);
$stmt->execute([':pk' => $project_key, ':ak' => $api_key]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid credentials']);
    exit;
}

$project_id = (int) $project['id'];

// Rate limiting: max 20 submissions per IP per hour
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
try {
    $rlStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM form_submissions
         WHERE project_id = ? AND ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $rlStmt->execute([$project_id, $ip]);
    if ((int)$rlStmt->fetchColumn() >= 20) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Too many submissions']);
        exit;
    }
} catch (Throwable $e) {
    // If ip_address column doesn't exist yet, skip rate limiting
}

// Normalise data to JSON string
if (is_array($data_raw)) {
    // Strip HTML tags from all string values to prevent stored XSS when viewed in admin
    array_walk_recursive($data_raw, function (&$val) {
        if (is_string($val)) $val = strip_tags($val);
    });
    $data_json = json_encode($data_raw, JSON_UNESCAPED_UNICODE);
} else {
    // Validate it is valid JSON if passed as string
    json_decode((string) $data_raw);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid data_json']);
        exit;
    }
    $data_json = (string) $data_raw;
}

// Sanitise form_type to alphanumeric + underscore
$form_type = preg_replace('/[^a-z0-9_]/', '', strtolower($form_type));
if (!$form_type) $form_type = 'contact';

// Insert submission
$ins = $pdo->prepare(
    'INSERT INTO form_submissions (project_id, form_type, data_json, created_at)
     VALUES (:pid, :ft, :dj, NOW())'
);
$ins->execute([
    ':pid' => $project_id,
    ':ft'  => $form_type,
    ':dj'  => $data_json,
]);

http_response_code(201);
echo json_encode(['ok' => true, 'id' => (int) $pdo->lastInsertId()]);
