<?php
/**
 * sync_from_json.php — Aktualisiert die CMS-DB aus einer content.json Datei.
 *
 * Quelle: data/live_content.json (manuell vom Live-Server heruntergeladen)
 *
 * Verwendung:
 *   ?token=bms2026seed          (Standard: liest data/live_content.json)
 *   ?token=bms2026seed&dry=1    (Nur lesen, nichts schreiben)
 *
 * Dieses Script löst das Problem, dass die CMS-DB und die Live-Website
 * auseinander gelaufen sind. Es nimmt die content.json vom Live-Server
 * als "Source of Truth" und schreibt sie in die CMS-DB zurück.
 *
 * Kann lokal UND auf zentra.services laufen.
 */
if (($_GET['token'] ?? '') !== 'bms2026seed') { http_response_code(403); die('403'); }

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/plain; charset=utf-8');
$dry = !empty($_GET['dry']);
$db  = db();

// ── 1. Projekt-ID ermitteln ──────────────────────────────────
$stmt = $db->prepare("SELECT id FROM projects WHERE project_key='flexfit' LIMIT 1");
$stmt->execute();
$project = $stmt->fetch();
if (!$project) die("FlexFit Projekt nicht gefunden in der DB.");
$pid = (int)$project['id'];

echo "=== Sync from JSON " . ($dry ? '(DRY RUN)' : '') . " ===\n";
echo "Projekt: FlexFit (ID {$pid})\n\n";

// ── 2. JSON laden ────────────────────────────────────────────
$json_path = __DIR__ . '/data/live_content.json';
if (!file_exists($json_path)) {
    die("FEHLER: {$json_path} nicht gefunden.\n\nLege die content.json vom Live-Server als data/live_content.json ab.");
}
$json = file_get_contents($json_path);
$data = json_decode($json, true);
if (!$data || empty($data['pages'])) die("FEHLER: Ungültiges JSON oder keine Seiten gefunden.");
echo "Quelle: " . basename($json_path) . " (" . strlen($json) . " Bytes, " . count($data['pages']) . " Seiten)\n\n";

if ($dry) {
    echo "--- DRY RUN: Zeige was passieren würde ---\n\n";
}

// ── 3. Bestehende Daten löschen ──────────────────────────────
if (!$dry) {
    $db->exec("DELETE sf FROM section_fields sf
               JOIN sections s ON s.id = sf.section_id
               JOIN pages p ON p.id = s.page_id
               WHERE p.project_id = {$pid}");
    echo "✅ section_fields geleert\n";

    $db->exec("DELETE si FROM section_items si
               JOIN sections s ON s.id = si.section_id
               JOIN pages p ON p.id = s.page_id
               WHERE p.project_id = {$pid}");
    echo "✅ section_items geleert\n\n";
} else {
    echo "[DRY] Würde section_fields + section_items leeren\n\n";
}

// ── 4. site_settings aktualisieren ───────────────────────────
$site = $data['site'] ?? [];
$settings_map = [
    'site_name'          => $site['name']               ?? '',
    'full_name'          => $site['full_name']           ?? '',
    'tagline'            => $site['tagline']             ?? '',
    'phone'              => $site['phone']               ?? '',
    'email'              => $site['email']               ?? '',
    'address'            => $site['address']             ?? '',
    'maps_url'           => $site['maps_url']            ?? '',
    'logo'               => $site['logo']                ?? '',
    'favicon'            => $site['favicon']             ?? '',
    'instagram_url'      => $site['instagram']           ?? '',
    'facebook_url'       => $site['facebook']            ?? '',
    'google_reviews_url' => $site['google_reviews_url']  ?? '',
    'description'        => $site['description']         ?? '',
];
foreach ($settings_map as $key => $val) {
    if ($val === '') continue;
    if (!$dry) {
        $db->prepare('INSERT INTO site_settings (project_id, setting_key, setting_value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')
           ->execute([$pid, $key, $val]);
    }
}
echo ($dry ? "[DRY] Würde" : "✅") . " site_settings aktualisiert (" . count(array_filter($settings_map)) . " Keys)\n\n";

// ── 5. Sektionen befüllen ────────────────────────────────────
$ok = 0; $skip = 0; $created = 0;

$is_repeater_array = function($arr): bool {
    if (!is_array($arr) || empty($arr)) return false;
    foreach ($arr as $v) {
        if (!is_array($v)) return false;
    }
    return true;
};

foreach ($data['pages'] as $page_slug => $page_data) {
    // Die DB nutzt Bindestriche, die content.json evtl. Underscores
    $db_slug = str_replace('_', '-', $page_slug);

    echo "── {$page_slug}" . ($db_slug !== $page_slug ? " → {$db_slug}" : '') . " ──\n";

    // Seite sicherstellen
    $page_check = $db->prepare('SELECT id FROM pages WHERE project_id=? AND slug=? LIMIT 1');
    $page_check->execute([$pid, $db_slug]);
    $page_row = $page_check->fetch();

    if (!$page_row) {
        // Seite existiert nicht in DB → erstellen
        if (!$dry) {
            $label = $page_data['label'] ?? ucfirst($db_slug);
            $db->prepare('INSERT INTO pages (project_id, slug, label, sort_order, created_at) VALUES (?,?,?,?,NOW())')
               ->execute([$pid, $db_slug, $label, 99]);
            $page_id = (int)$db->lastInsertId();
            echo "  ✅ Seite '{$db_slug}' erstellt (ID {$page_id})\n";
        } else {
            echo "  [DRY] Würde Seite '{$db_slug}' erstellen\n";
            $page_id = 0;
        }
    } else {
        $page_id = (int)$page_row['id'];
    }

    // SEO aktualisieren
    $seo = $page_data['seo'] ?? [];
    if ($seo && $page_id > 0 && !$dry) {
        $db->prepare('UPDATE pages SET seo_title=?, seo_description=?, og_title=?, og_description=?, og_image=?, noindex=? WHERE id=?')
           ->execute([
               $seo['title'] ?? '', $seo['description'] ?? '',
               $seo['og_title'] ?? '', $seo['og_description'] ?? '',
               $seo['og_image'] ?? '', (int)($seo['noindex'] ?? 0),
               $page_id
           ]);
    }

    // Sektionen
    foreach ($page_data['sections'] ?? [] as $sec_key => $sec) {
        $s = $db->prepare('SELECT s.id FROM sections s JOIN pages p ON p.id=s.page_id WHERE p.project_id=? AND p.slug=? AND s.section_key=? LIMIT 1');
        $s->execute([$pid, $db_slug, $sec_key]);
        $row = $s->fetch();

        if (!$row) {
            // Section existiert nicht → erstellen
            if (!$dry && $page_id > 0) {
                $section_label = $sec['label'] ?? ucfirst($sec_key);
                $db->prepare('INSERT INTO sections (page_id, section_key, label, section_type, enabled, sort_order, created_at) VALUES (?,?,?,?,?,?,NOW())')
                   ->execute([$page_id, $sec_key, $section_label, 'custom', (int)($sec['enabled'] ?? 1), 99]);
                $sid = (int)$db->lastInsertId();
                echo "  ✅ Section '{$sec_key}' erstellt (ID {$sid})\n";
                $created++;
            } else {
                echo "  [DRY] Würde Section '{$sec_key}' erstellen\n";
                continue;
            }
        } else {
            $sid = (int)$row['id'];
        }

        if ($dry) {
            $d = $sec['data'] ?? [];
            $field_count = count(array_filter($d, fn($v) => !is_array($v) || !$is_repeater_array($v)));
            $item_count  = 0;
            foreach ($d as $v) { if (is_array($v) && $is_repeater_array($v)) { $item_count += count($v); break; } }
            echo "  [DRY] {$sec_key}: ~{$field_count} fields, ~{$item_count} items\n";
            $ok++;
            continue;
        }

        $d = $sec['data'] ?? [];

        // Collect repeater arrays vs scalar fields
        $repeater_data = [];
        $scalar_fields = [];

        foreach ($d as $field_key => $field_val) {
            if ($field_key === 'items') continue;
            if (is_array($field_val) && $is_repeater_array($field_val)) {
                $repeater_data[$field_key] = $field_val;
            } else {
                $scalar_fields[$field_key] = $field_val;
            }
        }

        // Scalar fields → section_fields
        foreach ($scalar_fields as $field_key => $field_val) {
            if (is_bool($field_val)) {
                $type = 'bool'; $store = $field_val ? '1' : '0';
            } elseif (is_array($field_val)) {
                $type = 'json'; $store = json_encode($field_val, JSON_UNESCAPED_UNICODE);
            } elseif (is_numeric($field_val) && !is_string($field_val)) {
                $type = 'number'; $store = (string)$field_val;
            } else {
                $type = 'text'; $store = (string)$field_val;
            }
            $db->prepare('INSERT INTO section_fields (section_id, field_key, field_value, field_type) VALUES (?,?,?,?)')
               ->execute([$sid, $field_key, $store, $type]);
        }

        // Determine which repeater array → section_items
        $item_aliases = ['items', 'features', 'specs', 'rooms', 'prices', 'steps', 'members', 'trainers', 'articles'];
        $chosen_items = null;
        $chosen_key   = null;

        if (isset($d['items']) && is_array($d['items'])) {
            $chosen_items = $d['items'];
            $chosen_key   = 'items';
        } else {
            foreach ($item_aliases as $alias) {
                if (isset($repeater_data[$alias])) {
                    $chosen_items = $repeater_data[$alias];
                    $chosen_key   = $alias;
                    break;
                }
            }
            if ($chosen_items === null && !empty($repeater_data)) {
                $chosen_key   = array_key_first($repeater_data);
                $chosen_items = $repeater_data[$chosen_key];
            }
        }

        // Remaining repeater arrays → JSON fields
        foreach ($repeater_data as $field_key => $field_val) {
            if ($field_key === $chosen_key) continue;
            $store = json_encode($field_val, JSON_UNESCAPED_UNICODE);
            $db->prepare('INSERT INTO section_fields (section_id, field_key, field_value, field_type) VALUES (?,?,?,?)')
               ->execute([$sid, $field_key, $store, 'json']);
        }

        // Insert chosen repeater items
        if ($chosen_items !== null) {
            $sort = 0;
            foreach ($chosen_items as $item) {
                $db->prepare('INSERT INTO section_items (section_id, sort_order, item_json) VALUES (?,?,?)')
                   ->execute([$sid, $sort++, json_encode($item, JSON_UNESCAPED_UNICODE)]);
            }
            echo "  ✅ {$sec_key}: " . count($scalar_fields) . " fields + " . count($chosen_items) . " items [{$chosen_key}]\n";
        } else {
            echo "  ✅ {$sec_key}: " . count($scalar_fields) . " fields\n";
        }
        $ok++;
    }
}

echo "\n═══════════════════════════════════════════\n";
echo ($dry ? "[DRY RUN] " : "✅ ") . "{$ok} Sektionen verarbeitet, {$skip} übersprungen, {$created} neu erstellt.\n";
if (!$dry) echo "CMS-Seite neu laden um Änderungen zu sehen.\n";
