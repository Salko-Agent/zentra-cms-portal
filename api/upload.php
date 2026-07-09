<?php
// ============================================================
// Zentra API – POST /api/upload.php
// Accepts image → GD resize → WebP → proxy to client site
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

$project_id = current_project_id();

// Validate uploaded file
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'No file uploaded']);
    exit;
}

$file     = $_FILES['file'];
$max_size = 10 * 1024 * 1024; // 10 MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'File too large (max 10 MB)']);
    exit;
}

// Validate MIME via finfo (not just extension)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mime     = $finfo->file($file['tmp_name']);
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime, $allowed_mimes, true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid file type']);
    exit;
}

// Convert to WebP with GD
$img = match($mime) {
    'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
    'image/png'  => imagecreatefrompng($file['tmp_name']),
    'image/gif'  => imagecreatefromgif($file['tmp_name']),
    'image/webp' => imagecreatefromwebp($file['tmp_name']),
    default      => null,
};

if (!$img) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Could not process image']);
    exit;
}

// Resize to max 1920px wide, keep ratio
$orig_w = imagesx($img);
$orig_h = imagesy($img);
$max_w  = 1920;
if ($orig_w > $max_w) {
    $new_w = $max_w;
    $new_h = (int)round($orig_h * ($max_w / $orig_w));
    $resized = imagecreatetruecolor($new_w, $new_h);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
    imagedestroy($img);
    $img = $resized;
}

$filename  = sprintf('%s_%s.webp', $project_id, bin2hex(random_bytes(8)));
$temp_path = sys_get_temp_dir() . '/' . $filename;
imagewebp($img, $temp_path, 85);
imagedestroy($img);

// Try to proxy to client site
$project   = get_project($project_id);
$settings  = get_settings($project_id);
$final_url = '';

if ($project) {
    // Prefer the dedicated upload_receiver_url setting; fall back to deriving from webhook_url
    if (!empty($settings['upload_receiver_url'])) {
        $upload_url = rtrim($settings['upload_receiver_url'], '/');
    } elseif (!empty($project['webhook_url'])) {
        $base = preg_replace('/_webhook\.php.*$/', '', $project['webhook_url']);
        $upload_url = $base . '_upload-receive.php';
    } else {
        $upload_url = '';
    }

    if ($upload_url !== '') {
    // Validate URL host (prevent SSRF)
    $host = parse_url($upload_url, PHP_URL_HOST);
    $is_private = $host && preg_match('/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|localhost)/i', $host);

    if (!$is_private && filter_var($upload_url, FILTER_VALIDATE_URL)) {
        $ch = curl_init($upload_url);
        $cf = new CURLFile($temp_path, 'image/webp', $filename);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['file' => $cf, 'key' => $project['api_key']],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($resp, true);
        if (!empty($decoded['url'])) {
            $final_url = $decoded['url'];
        }
    }
    }
}

// Fallback: store on zentra.services itself
if ($final_url === '') {
    $local_dir = dirname(__DIR__) . '/uploads/' . $project_id . '/';
    if (!is_dir($local_dir)) mkdir($local_dir, 0755, true);
    rename($temp_path, $local_dir . $filename);
    $final_url = APP_URL . '/uploads/' . $project_id . '/' . $filename;
} else {
    @unlink($temp_path);
}

// Record in media table
$stmt = db()->prepare(
    'INSERT INTO media (project_id, filename, original_name, url, file_size, mime_type)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stored_size = isset($local_dir) ? (int)@filesize($local_dir . $filename) : (int)$file['size'];
$stmt->execute([$project_id, $filename, basename($file['name']), $final_url, $stored_size, 'image/webp']);

log_activity($project_id, 'media_uploaded', 'media', null, basename($file['name']));

echo json_encode(['ok' => true, 'url' => $final_url]);
