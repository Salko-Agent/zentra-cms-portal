<?php
// ============================================================
// Zentra API – GET /api/content.php
// FlexFit site calls this to pull all content as JSON
// Auth: ?project=flexfit&key=API_KEY
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Rate limiting via DB? Keep it simple: check API key only
$project_key = trim($_GET['project'] ?? '');
$api_key     = trim($_GET['key'] ?? '');

if ($project_key === '' || $api_key === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing project or key']);
    exit;
}

// Validate project
$stmt = db()->prepare('SELECT id, active FROM projects WHERE project_key = ? AND api_key = ? LIMIT 1');
$stmt->execute([$project_key, $api_key]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

if (!$project['active']) {
    http_response_code(403);
    echo json_encode(['error' => 'Project inactive']);
    exit;
}

$project_id = (int)$project['id'];

// Optional: serve a specific page only (smaller payload)
$page_filter = trim($_GET['page'] ?? '');

$content = load_content($project_id);

// Transform to match content.json format expected by FlexFit templates
$content = transform_for_website($content);

if ($page_filter !== '') {
    if (!isset($content['pages'][$page_filter])) {
        http_response_code(404);
        echo json_encode(['error' => 'Page not found']);
        exit;
    }
    echo json_encode([
        'site'   => $content['site'],
        'pages'  => [$page_filter => $content['pages'][$page_filter]],
        'global' => $content['global'],
        'meta'   => ['cms_version' => '2.0.0'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array_merge($content, ['meta' => ['cms_version' => '2.0.0', 'last_updated' => date('Y-m-d')]]), JSON_UNESCAPED_UNICODE);

// ── Transform DB format → content.json format ─────────────
function transform_for_website(array $content): array {
    // Section-specific key names for "items" (section_items → named key)
    $items_aliases = [
        'why'             => 'features',
        'trainer'         => 'specs',
        'ablauf'          => 'steps',
        'raumvermietung'  => 'rooms',
        'gruppentraining' => 'prices',   // FlexFit: Gruppentraining Preisliste
        // datenschutz 'content' section: items → sections
    ];

    // Fields that are stored as JSON strings but should be native arrays
    $json_fields = [
        // existing
        'goals', 'specializations', 'spezialisierungen', 'mitbringen', 'vorteile',
        // FlexFit: studio_info
        'paragraphs', 'features',
        // FlexFit: physiotherapie leistungen
        'intro_paragraphs', 'leistungen_items', 'spezialisierungen_cards',
        'ablauf_steps', 'preise_items',
        // FlexFit: physiotherapie was_ist_physio
        'categories',
        // FlexFit: raummiete
        'vorteile_cards',
        // FlexFit: firmenfitness angebot (already items, but alias for clarity)
        'prices',
    ];

    foreach ($content['pages'] as &$page) {
        foreach ($page['sections'] as $sec_key => &$section) {
            $data = &$section['data'];

            // ── Hero section: normalise field naming ──────────────────────────
            // DB stores headline_line1/2 and rating; website templates expect
            // headline_sub and trust_rating/trust_count.
            if ($sec_key === 'hero') {
                // headline_line1 → headline_sub  (keep both for backwards compat)
                if (isset($data['headline_line1']) && !isset($data['headline_sub'])) {
                    $data['headline_sub'] = $data['headline_line1'];
                }
                // rating → trust_rating
                if (isset($data['rating']) && !isset($data['trust_rating'])) {
                    $data['trust_rating'] = $data['rating'];
                }
                // trust_count: prefer explicit field, else parse from trust_text
                if (!isset($data['trust_count'])) {
                    if (isset($data['trust_text'])) {
                        // Match a number before "Google" e.g. "65 Google-Bewertungen"
                        if (preg_match('/(\d+)\s*Google/i', $data['trust_text'], $m)) {
                            $data['trust_count'] = $m[1];
                        }
                    }
                }
                // goals: if stored as JSON string, decode to array
                if (isset($data['goals']) && is_string($data['goals'])) {
                    $decoded = json_decode($data['goals'], true);
                    if (is_array($decoded)) {
                        $data['goals'] = $decoded;
                    } else {
                        $lines = array_values(array_filter(array_map('trim', explode("\n", $data['goals']))));
                        if (!empty($lines)) $data['goals'] = $lines;
                    }
                }
            }

            // Rename 'items' to section-specific key if alias exists
            if (isset($data['items']) && isset($items_aliases[$sec_key])) {
                $alias = $items_aliases[$sec_key];
                $data[$alias] = $data['items'];
                unset($data['items']);
            }

            // For datenschutz content section: items → sections
            if ($sec_key === 'content' && isset($data['items'])) {
                $data['sections'] = $data['items'];
                unset($data['items']);
            }

            // Parse JSON-string fields into native arrays
            foreach ($json_fields as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    $decoded = json_decode($data[$field], true);
                    if (is_array($decoded)) {
                        $data[$field] = $decoded;
                    } else {
                        // Try newline-separated
                        $lines = array_values(array_filter(array_map('trim', explode("\n", $data[$field]))));
                        if (!empty($lines)) $data[$field] = $lines;
                    }
                }
            }

            // Remove internal fields not needed by website
            unset($section['id'], $section['section_type'], $section['sort_order']);
        }
        unset($section);
    }
    unset($page);

    return $content;
}
