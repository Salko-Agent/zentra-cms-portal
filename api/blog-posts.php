<?php
// api/blog-posts.php — Public GET endpoint for blog posts
// Scoped strictly to one project via api_key
// Usage: GET /api/blog-posts.php?project=KEY&key=API_KEY[&slug=SLUG]

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$project_key = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['project'] ?? ''));
$api_key     = trim($_GET['key'] ?? '');
$slug        = preg_replace('/[^a-z0-9\-]/', '', $_GET['slug'] ?? '');

if (!$project_key || !$api_key) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing project or key']);
    exit;
}

$db = db();

// Verify project + api_key — NEVER expose other projects
$stmt = $db->prepare('SELECT id FROM projects WHERE project_key = ? AND api_key = ? LIMIT 1');
$stmt->execute([$project_key, $api_key]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$project_id = (int)$project['id'];

if ($slug) {
    // Single post
    $stmt = $db->prepare(
        'SELECT id, slug, title, excerpt, featured_image, author_name, author_photo,
                category, read_time, status, content, seo_title, seo_description, published_at
         FROM blog_posts
         WHERE project_id = ? AND slug = ? AND status = "published"
         LIMIT 1'
    );
    $stmt->execute([$project_id, $slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    $post['content'] = json_decode($post['content'] ?: '[]', true);
    echo json_encode(['post' => $post]);

} else {
    // All published posts, newest first
    $stmt = $db->prepare(
        'SELECT id, slug, title, excerpt, featured_image, author_name, author_photo,
                category, read_time, published_at
         FROM blog_posts
         WHERE project_id = ? AND status = "published"
         ORDER BY published_at DESC, id DESC'
    );
    $stmt->execute([$project_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['posts' => $posts]);
}
