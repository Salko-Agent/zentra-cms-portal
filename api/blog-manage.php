<?php
// ============================================================
// Zentra API – POST /api/blog-manage.php
// Blog post CRUD: save (insert/update), delete
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
$action     = trim($_POST['action'] ?? '');
$db         = db();

// ── Helper: sanitize slug ─────────────────────────────────────
function sanitize_blog_slug(string $raw): string {
    return preg_replace('/[^a-z0-9\-]/', '', strtolower($raw));
}

switch ($action) {

    // ── Save (insert or update) ───────────────────────────────
    case 'save':
        $blog_post_id = (int)($_POST['blog_post_id'] ?? 0);
        $is_new       = ($blog_post_id === 0);

        // Required fields
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            echo json_encode(['ok' => false, 'error' => 'Titel ist erforderlich']);
            exit;
        }

        $slug = sanitize_blog_slug(trim($_POST['slug'] ?? ''));
        if ($slug === '') {
            echo json_encode(['ok' => false, 'error' => 'Slug ist erforderlich und darf nur Kleinbuchstaben, Zahlen und Bindestriche enthalten']);
            exit;
        }

        // Validate content_json
        $content_json_raw = $_POST['content_json'] ?? '';
        if ($content_json_raw === '' || $content_json_raw === null) {
            $content_json_raw = '[]';
        }
        $content_decoded = json_decode($content_json_raw, true);
        if (!is_array($content_decoded)) {
            echo json_encode(['ok' => false, 'error' => 'Ungültiges Inhaltsformat (kein gültiges JSON)']);
            exit;
        }
        // Re-encode to normalise
        $content_json = json_encode($content_decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Optional fields
        $excerpt         = trim($_POST['excerpt']         ?? '');
        $featured_image  = trim($_POST['featured_image']  ?? '');
        $author_name     = trim($_POST['author_name']     ?? '');
        $author_photo    = trim($_POST['author_photo']    ?? '');
        $category        = trim($_POST['category']        ?? '');
        $read_time       = max(0, min(255, (int)($_POST['read_time'] ?? 0)));
        $seo_title       = mb_substr(trim($_POST['seo_title']       ?? ''), 0, 70);
        $seo_description = mb_substr(trim($_POST['seo_description'] ?? ''), 0, 160);

        $status_raw = trim($_POST['status'] ?? 'draft');
        $status     = in_array($status_raw, ['draft', 'published'], true) ? $status_raw : 'draft';

        // published_at logic
        $published_at_input = trim($_POST['published_at'] ?? '');
        if ($status === 'published') {
            if ($published_at_input !== '') {
                // Normalise datetime-local value to MySQL DATETIME
                $ts = strtotime($published_at_input);
                $published_at = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
            } else {
                $published_at = date('Y-m-d H:i:s');
            }
        } else {
            $published_at = null;
        }

        if ($is_new) {
            // Check slug uniqueness for this project
            $chk = $db->prepare('SELECT id FROM blog_posts WHERE project_id = ? AND slug = ?');
            $chk->execute([$project_id, $slug]);
            if ($chk->fetch()) {
                echo json_encode(['ok' => false, 'error' => 'Ein Beitrag mit diesem Slug existiert bereits']);
                exit;
            }

            try {
                $ins = $db->prepare(
                    'INSERT INTO blog_posts
                       (project_id, slug, title, excerpt, featured_image,
                        author_name, author_photo, category, read_time,
                        status, content, seo_title, seo_description, published_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $ins->execute([
                    $project_id, $slug, $title, $excerpt, $featured_image,
                    $author_name, $author_photo, $category, $read_time,
                    $status, $content_json, $seo_title, $seo_description, $published_at,
                ]);
                $new_id = (int)$db->lastInsertId();

                echo json_encode([
                    'ok'       => true,
                    'id'       => $new_id,
                    'redirect' => '/admin/blog-edit.php?id=' . $new_id,
                ]);
            } catch (Throwable $e) {
                error_log('blog insert error: ' . $e->getMessage());
                echo json_encode(['ok' => false, 'error' => 'Datenbankfehler beim Erstellen']);
            }
            exit;
        }

        // Update existing — verify ownership first
        $own = $db->prepare('SELECT id FROM blog_posts WHERE id = ? AND project_id = ?');
        $own->execute([$blog_post_id, $project_id]);
        if (!$own->fetch()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Beitrag nicht gefunden']);
            exit;
        }

        // Check slug uniqueness (exclude self)
        $chk2 = $db->prepare('SELECT id FROM blog_posts WHERE project_id = ? AND slug = ? AND id != ?');
        $chk2->execute([$project_id, $slug, $blog_post_id]);
        if ($chk2->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Ein anderer Beitrag mit diesem Slug existiert bereits']);
            exit;
        }

        try {
            $upd = $db->prepare(
                'UPDATE blog_posts SET
                   slug             = ?,
                   title            = ?,
                   excerpt          = ?,
                   featured_image   = ?,
                   author_name      = ?,
                   author_photo     = ?,
                   category         = ?,
                   read_time        = ?,
                   status           = ?,
                   content          = ?,
                   seo_title        = ?,
                   seo_description  = ?,
                   published_at     = ?
                 WHERE id = ? AND project_id = ?'
            );
            $upd->execute([
                $slug, $title, $excerpt, $featured_image,
                $author_name, $author_photo, $category, $read_time,
                $status, $content_json, $seo_title, $seo_description,
                $published_at,
                $blog_post_id, $project_id,
            ]);

            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            error_log('blog update error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Datenbankfehler beim Speichern']);
        }
        break;

    // ── Delete ────────────────────────────────────────────────
    case 'delete':
        $blog_post_id = (int)($_POST['blog_post_id'] ?? 0);
        if ($blog_post_id <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Fehlende blog_post_id']);
            exit;
        }

        // Verify ownership
        $own = $db->prepare('SELECT id, title FROM blog_posts WHERE id = ? AND project_id = ?');
        $own->execute([$blog_post_id, $project_id]);
        $post = $own->fetch();
        if (!$post) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Beitrag nicht gefunden']);
            exit;
        }

        try {
            $db->prepare('DELETE FROM blog_posts WHERE id = ? AND project_id = ?')
               ->execute([$blog_post_id, $project_id]);

            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            error_log('blog delete error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Datenbankfehler beim Löschen']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Unbekannte Aktion']);
}
