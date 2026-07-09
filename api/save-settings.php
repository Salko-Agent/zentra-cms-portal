<?php
// ============================================================
// Zentra API – POST /api/save-settings.php
// Saves site_settings key→value pairs
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

// Accept only known keys
$allowed_keys = [
    // General
    'site_name','site_tagline','description','phone','email','address',
    'maps_url','logo','favicon','google_reviews_url','footer_tagline',
    // Social
    'instagram_url','facebook_url',
    // Tracking
    'ga_id','gtm_id','meta_pixel_id',
    'head_custom_scripts','body_custom_scripts',
    // Webhook / API
    'webhook_url','upload_receiver_url',
    // Google API Integration
    'ga4_property_id','gsc_property','pagespeed_api_key',
    // Live Preview
    'preview_url',
    // Legacy keys (backwards compat)
    'name','full_name','tagline','instagram','facebook',
    'google_analytics_id','google_tag_manager_id',
];

$project_id = current_project_id();
foreach ($allowed_keys as $key) {
    if (array_key_exists($key, $body)) {
        save_setting($project_id, $key, (string)$body[$key]);
    }
}

// Sync webhook_url to projects table (that's where ping_webhook() reads from)
if (array_key_exists('webhook_url', $body)) {
    db()->prepare('UPDATE projects SET webhook_url = ? WHERE id = ?')
        ->execute([(string)$body['webhook_url'], $project_id]);
}

// Sync project_domain to projects table (used by Google Indexing API)
if (array_key_exists('project_domain', $body)) {
    $domain = rtrim(trim((string)$body['project_domain']), '/');
    db()->prepare('UPDATE projects SET domain = ? WHERE id = ?')
        ->execute([$domain, $project_id]);
}

ping_webhook($project_id);
log_activity($project_id, 'settings_saved', 'settings');
echo json_encode(['ok' => true]);
