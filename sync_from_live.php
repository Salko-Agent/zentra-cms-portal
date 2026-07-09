<?php
/**
 * sync_from_live.php — Holt Content von zentra.services und schreibt ihn in die lokale DB.
 * NUR für lokale Entwicklung. NIEMALS hochladen.
 * URL: http://localhost:8081/sync_from_live.php?token=bms2026seed
 */
if (($_GET['token'] ?? '') !== 'bms2026seed') { http_response_code(403); die('403'); }

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$LIVE_API = 'https://zentra.services/api/content.php?project=flexfit&key=sf_live_13b604909e5bd1ff6b080fb5ae17762baf2a1a9fe2b333b9';

echo "<pre>";
echo "Lade Content von Live-Server...\n";

$ctx = stream_context_create(['http' => ['timeout' => 15]]);
$json = @file_get_contents($LIVE_API, false, $ctx);
if (!$json) { die("FEHLER: Konnte Live-API nicht erreichen."); }

$data = json_decode($json, true);
if (!$data || empty($data['pages'])) { die("FEHLER: Ungültige API-Antwort.\n" . substr($json, 0, 200)); }

echo "✅ Content geladen (" . strlen($json) . " Bytes, " . count($data['pages']) . " Seiten)\n\n";

$db = db();
$stmt = $db->prepare("SELECT id FROM projects WHERE project_key='flexfit' LIMIT 1");
$stmt->execute();
$project = $stmt->fetch();
if (!$project) die("FlexFit project nicht gefunden.");
$pid = (int)$project['id'];

// Helper: get section id — scoped to project via pages.project_id
function get_sid(PDO $db, int $pid, string $page_slug, string $sec_key): ?int {
    $s = $db->prepare('SELECT s.id FROM sections s JOIN pages p ON p.id=s.page_id WHERE p.project_id=? AND p.slug=? AND s.section_key=? LIMIT 1');
    $s->execute([$pid, $page_slug, $sec_key]);
    $r = $s->fetch();
    return $r ? (int)$r['id'] : null;
}

// Helper: upsert field (UTF-8 safe via PDO)
function set_field(PDO $db, int $sid, string $key, string $value, string $type = 'text'): void {
    $db->prepare('INSERT INTO section_fields (section_id, field_key, field_value, field_type) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE field_value=VALUES(field_value), field_type=VALUES(field_type)')
       ->execute([$sid, $key, $value, $type]);
}

// Helper: set items
function set_items(PDO $db, int $sid, array $items): void {
    $db->prepare('DELETE FROM section_items WHERE section_id=?')->execute([$sid]);
    foreach (array_values($items) as $i => $item) {
        $db->prepare('INSERT INTO section_items (section_id, sort_order, item_json) VALUES (?,?,?)')->execute([$sid, $i, json_encode($item, JSON_UNESCAPED_UNICODE)]);
    }
}

// ── Sync site_settings ────────────────────────────────────────
$site = $data['site'] ?? [];
$mapping = [
    'site_name'   => $site['name']          ?? '',
    'phone'       => $site['phone']         ?? '',
    'email'       => $site['email']         ?? '',
    'address'     => $site['address']       ?? '',
    'maps_url'    => $site['maps_url']      ?? '',
    'logo'        => $site['logo']          ?? '',
    'instagram_url' => $site['instagram']   ?? '',
    'facebook_url'  => $site['facebook']    ?? '',
    'description'   => $site['description'] ?? '',
];
foreach ($mapping as $key => $val) {
    if ($val === '') continue;
    $db->prepare('INSERT INTO site_settings (project_id, setting_key, setting_value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')
       ->execute([$pid, $key, $val]);
}
echo "✅ site_settings synchronisiert\n";

// ── Sync section fields + items ───────────────────────────────
$ok = 0; $skip = 0;
foreach ($data['pages'] as $page_slug => $page_data) {
    // Normalize slug (API uses underscore, DB uses hyphen)
    $db_slug = str_replace('_', '-', $page_slug);

    foreach ($page_data['sections'] ?? [] as $sec_key => $sec) {
        if (empty($sec['enabled']) && !isset($sec['data'])) { $skip++; continue; }
        $sid = get_sid($db, $pid, $db_slug, $sec_key);
        if (!$sid) { echo "  ⚠ Nicht gefunden: {$db_slug} › {$sec_key}\n"; $skip++; continue; }

        $d = $sec['data'] ?? [];
        foreach ($d as $field_key => $field_val) {
            if ($field_key === 'items') continue; // handled separately
            $type = is_bool($field_val) ? 'bool' : (is_array($field_val) ? 'json' : 'text');
            $store = is_array($field_val) ? json_encode($field_val, JSON_UNESCAPED_UNICODE) : (string)$field_val;
            set_field($db, $sid, $field_key, $store, $type);
        }

        if (isset($d['items']) && is_array($d['items'])) {
            set_items($db, $sid, $d['items']);
        }

        $ok++;
    }

    // Sync page SEO
    $seo = $page_data['seo'] ?? [];
    if ($seo) {
        $db->prepare('UPDATE pages SET seo_title=?, seo_description=?, og_title=?, og_description=?, og_image=?, noindex=? WHERE project_id=? AND slug=?')
           ->execute([
               $seo['title'] ?? '', $seo['description'] ?? '',
               $seo['og_title'] ?? '', $seo['og_description'] ?? '',
               $seo['og_image'] ?? '', (int)($seo['noindex'] ?? 0),
               $pid, $db_slug
           ]);
    }
}

echo "\n✅ {$ok} Sektionen synchronisiert, {$skip} übersprungen.\n";
echo "\n<strong>Fertig! Seite neu laden.</strong>\n";
echo "</pre>";
