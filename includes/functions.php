<?php
// ============================================================
// Zentra – Shared helper functions
// ============================================================

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Content loading ─────────────────────────────────────────

/**
 * Load all pages + sections + fields + items for a project.
 * Returns a structured array mirroring content.json v2.0.0 format.
 */
function load_content(int $project_id): array {
    require_once __DIR__ . '/db.php';
    $db = db();

    // Site settings
    $stmt = $db->prepare('SELECT setting_key, setting_value FROM site_settings WHERE project_id = ?');
    $stmt->execute([$project_id]);
    $site = [];
    foreach ($stmt->fetchAll() as $row) {
        $site[$row['setting_key']] = $row['setting_value'];
    }

    // All pages
    $stmt = $db->prepare('SELECT * FROM pages WHERE project_id = ? ORDER BY id');
    $stmt->execute([$project_id]);
    $pages_raw = $stmt->fetchAll();

    $pages = [];
    foreach ($pages_raw as $page) {
        $slug = $page['slug'];
        $pages[$slug] = [
            'id'  => (int)$page['id'],
            'label' => $page['label'],
            'seo' => [
                'title'           => $page['seo_title'],
                'description'     => $page['seo_description'],
                'og_title'        => $page['og_title'],
                'og_description'  => $page['og_description'],
                'og_image'        => $page['og_image'],
                'noindex'         => (bool)$page['noindex'],
            ],
            'sections' => load_sections((int)$page['id'], $db),
        ];
    }

    // Tracking — support both legacy and new key names
    $ga_id          = $site['ga_id']          ?? $site['google_analytics_id']   ?? '';
    $gtm_id         = $site['gtm_id']         ?? $site['google_tag_manager_id'] ?? '';
    $meta_pixel_id  = $site['meta_pixel_id']  ?? '';
    $head_scripts   = $site['head_custom_scripts'] ?? '';
    $body_scripts   = $site['body_custom_scripts'] ?? '';

    return [
        'site'   => $site,
        'pages'  => $pages,
        'global' => [
            'tracking' => [
                'ga_id'               => $ga_id,
                'gtm_id'              => $gtm_id,
                'meta_pixel_id'       => $meta_pixel_id,
                'head_custom_scripts' => $head_scripts,
                'body_custom_scripts' => $body_scripts,
                // Legacy keys (backwards compat)
                'google_analytics_id'   => $ga_id,
                'google_tag_manager_id' => $gtm_id,
            ],
            'footer' => ['tagline' => $site['footer_tagline'] ?? ''],
        ],
    ];
}

function load_sections(int $page_id, PDO $db): array {
    $stmt = $db->prepare('SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order, id');
    $stmt->execute([$page_id]);
    $sections_raw = $stmt->fetchAll();

    $sections = [];
    foreach ($sections_raw as $sec) {
        $sid = (int)$sec['id'];
        $sections[$sec['section_key']] = [
            'id'           => $sid,
            'label'        => $sec['label'],
            'section_type' => $sec['section_type'],
            'enabled'      => (bool)$sec['enabled'],
            'sort_order'   => (int)$sec['sort_order'],
            'data'         => load_section_data($sid, $db),
        ];
    }
    return $sections;
}

function load_section_data(int $section_id, PDO $db): array {
    // Fields (scalars)
    $stmt = $db->prepare('SELECT field_key, field_value, field_type FROM section_fields WHERE section_id = ?');
    $stmt->execute([$section_id]);
    $data = [];
    foreach ($stmt->fetchAll() as $f) {
        $val = $f['field_value'];
        if ($f['field_type'] === 'bool') {
            $val = (bool)$val;
        } elseif ($f['field_type'] === 'number') {
            $val = is_numeric($val) ? (float)$val : $val;
        }
        $data[$f['field_key']] = $val;
    }

    // Items (repeater rows)
    $stmt = $db->prepare('SELECT item_json FROM section_items WHERE section_id = ? ORDER BY sort_order, id');
    $stmt->execute([$section_id]);
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $decoded = json_decode($row['item_json'], true);
        if ($decoded !== null) $items[] = $decoded;
    }
    if (!empty($items)) {
        $data['items'] = $items;
    }

    return $data;
}

// ── Project helpers ──────────────────────────────────────────

function get_all_projects(): array {
    require_once __DIR__ . '/db.php';
    return db()->query('SELECT id, name, domain FROM projects ORDER BY id')->fetchAll();
}

function get_project(int $project_id): ?array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
    $stmt->execute([$project_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_all_pages(int $project_id): array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT p.*, (SELECT COUNT(*) FROM sections s WHERE s.page_id = p.id) as section_count FROM pages p WHERE p.project_id = ? ORDER BY p.id');
    $stmt->execute([$project_id]);
    return $stmt->fetchAll();
}

function get_page_by_slug(int $project_id, string $slug): ?array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT * FROM pages WHERE project_id = ? AND slug = ? LIMIT 1');
    $stmt->execute([$project_id, $slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_sections_for_page(int $page_id): array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order, id');
    $stmt->execute([$page_id]);
    return $stmt->fetchAll();
}

function get_section_fields(int $section_id): array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT field_key, field_value, field_type FROM section_fields WHERE section_id = ?');
    $stmt->execute([$section_id]);
    $out = [];
    foreach ($stmt->fetchAll() as $f) {
        $out[$f['field_key']] = ['value' => $f['field_value'], 'type' => $f['field_type']];
    }
    return $out;
}

function get_section_items(int $section_id): array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT id, sort_order, item_json FROM section_items WHERE section_id = ? ORDER BY sort_order, id');
    $stmt->execute([$section_id]);
    return $stmt->fetchAll();
}

// ── Webhook ping (non-blocking) ──────────────────────────────

function ping_webhook(int $project_id): void {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT webhook_url, api_key FROM projects WHERE id = ?');
    $stmt->execute([$project_id]);
    $proj = $stmt->fetch();
    if (!$proj || empty($proj['webhook_url'])) return;

    $url  = filter_var($proj['webhook_url'], FILTER_VALIDATE_URL);
    if (!$url) return;

    // Validate it's an http(s) URL (prevent SSRF to internal IPs)
    $host = parse_url($url, PHP_URL_HOST);
    if ($host && preg_match('/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|localhost)/i', $host)) {
        return; // Refuse to ping private/loopback addresses
    }

    $payload = json_encode(['project' => $project_id, 'ts' => time()]);
    $sig = hash_hmac('sha256', $payload, $proj['api_key']);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Zentra-Sig: ' . $sig,
        ],
    ]);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($result === false || $http_code >= 400) {
        error_log('Zentra webhook failed: ' . $url . ' | HTTP ' . $http_code . ' | curl: ' . curl_error($ch) . ' | response: ' . substr((string)$result, 0, 200));
    }
    curl_close($ch);
}

// ── Activity log ────────────────────────────────────────────

function log_activity(int $project_id, string $action, string $target_type = '', ?int $target_id = null, string $target_label = '', ?array $meta = null): void {
    try {
        require_once __DIR__ . '/db.php';
        $user_id = $_SESSION['user_id'] ?? null;
        db()->prepare(
            'INSERT INTO activity_log (project_id, user_id, action, target_type, target_id, target_label, meta_json)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $project_id,
            $user_id,
            $action,
            $target_type,
            $target_id,
            $target_label,
            $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
        error_log('activity log error: ' . $e->getMessage());
    }
}

// ── Live Preview Token ───────────────────────────────────────

/**
 * Generate a time-limited preview token for a project.
 * Token = HMAC-SHA256(api_key, "zentra-preview:{hour_bucket}")
 * Valid for the current hour + previous hour (1–2 h window).
 */
function generate_preview_token(string $api_key): string {
    $ts = (string)floor(time() / 3600);
    return hash_hmac('sha256', 'zentra-preview:' . $ts, $api_key);
}

// ── Settings helpers ─────────────────────────────────────────

function get_settings(int $project_id): array {
    require_once __DIR__ . '/db.php';
    $stmt = db()->prepare('SELECT setting_key, setting_value FROM site_settings WHERE project_id = ?');
    $stmt->execute([$project_id]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[$r['setting_key']] = $r['setting_value'];
    }
    return $out;
}

function save_setting(int $project_id, string $key, string $value): void {
    require_once __DIR__ . '/db.php';
    db()->prepare(
        'INSERT INTO site_settings (project_id, setting_key, setting_value)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute([$project_id, $key, $value]);
}

// ── Section types ───────────────────────────────────────────

function get_available_section_types(): array {
    return [
        'hero'            => 'Hero Banner',
        'stats_strip'     => 'Statistik-Leiste',
        'partners'        => 'Partner / Logos',
        'image_text'      => 'Bild & Text',
        'list_items'      => 'Listen-Einträge',
        'team_members'    => 'Team-Mitglieder',
        'trainer_profile' => 'Trainer-Profil',
        'testimonials'    => 'Kundenstimmen',
        'pricing_cards'   => 'Preiskarten',
        'cta'             => 'Call to Action',
        'steps'           => 'Schritte / Ablauf',
        'price_table'     => 'Preistabelle',
        'contact_info'    => 'Kontaktinformationen',
        'blog_articles'   => 'Blog-Artikel',
        'legal'           => 'Rechtliches / Impressum',
        'room_listing'    => 'Raumliste / Bereiche',
        'faq'             => 'FAQ / Häufige Fragen',
        'portfolio'       => 'Portfolio / Projekte',
        'timeline'        => 'Timeline / Meilensteine',
    ];
}
