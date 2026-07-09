<?php
// ============================================================
// Zentra API – POST /api/preview.php
// Renders a FlexFit page server-side for the admin iframe
// Auth: session OR one-time preview token
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

auth_start();

// Accept project from GET (for iframe use), must match session project
$slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['page'] ?? 'home'));

// Auth: must be logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo '<p>Access denied</p>';
    exit;
}

$project_id  = current_project_id();
$content     = load_content($project_id);
$project_row = get_project($project_id);
$project_key = $project_row['project_key'] ?? 'flexfit';

require_once dirname(__DIR__) . '/preview/renderer.php';
render_page($slug, $content, $project_key);
