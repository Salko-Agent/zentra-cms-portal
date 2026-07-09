<?php
// ============================================================
// Zentra API – POST /api/refresh-analytics.php
// Manual refresh of analytics data for current project
// Rate-limited to once per hour
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics-fetchers.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();
csrf_verify();

$project_id = current_project_id();

// Rate limit: check last fetch time
$stmt = db()->prepare("SELECT MAX(fetched_at) as last FROM analytics_cache WHERE project_id = ?");
$stmt->execute([$project_id]);
$last = $stmt->fetchColumn();
if ($last && strtotime($last) > time() - 3600) {
    echo json_encode(['ok' => false, 'error' => 'Daten wurden vor weniger als 1 Stunde aktualisiert. Bitte warte.']);
    exit;
}

$errors = [];
$ranges = ['7d', '30d', '90d'];
$settings = get_settings($project_id);

// GA4
if (!empty($settings['ga4_property_id'])) {
    foreach ($ranges as $range) {
        try {
            fetch_ga4_overview($project_id, $range);
            fetch_ga4_daily_visitors($project_id, $range);
            fetch_ga4_top_pages($project_id, $range);
            fetch_ga4_traffic_sources($project_id, $range);
        } catch (Throwable $e) {
            $errors[] = "GA4 ($range): " . $e->getMessage();
        }
    }
}

// GSC
if (!empty($settings['gsc_property'])) {
    foreach ($ranges as $range) {
        try {
            fetch_gsc_overview($project_id, $range);
            fetch_gsc_keywords($project_id, $range);
            fetch_gsc_pages($project_id, $range);
        } catch (Throwable $e) {
            $errors[] = "GSC ($range): " . $e->getMessage();
        }
    }
}

if (!empty($errors)) {
    error_log("Zentra refresh-analytics for project $project_id: " . implode(' | ', $errors));
}

echo json_encode(['ok' => true, 'refreshed' => empty($errors), 'error_count' => count($errors)]);
