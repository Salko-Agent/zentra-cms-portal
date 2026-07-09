<?php
// ============================================================
// Zentra API – GET /api/cron-analytics.php
// Automated daily data refresh for all projects
// Triggered via cPanel cron or external service
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics-fetchers.php';

header('Content-Type: application/json; charset=utf-8');

// Token authentication
$token = $_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? '';
if (!$token || !hash_equals(CRON_SECRET, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = db();
$projects = $db->query("SELECT id FROM projects")->fetchAll();
$results = [];

foreach ($projects as $p) {
    $pid = (int)$p['id'];
    $settings = get_settings($pid);
    $projectResults = ['project_id' => $pid, 'ga4' => 'skipped', 'gsc' => 'skipped', 'pagespeed' => 'skipped'];

    // GA4: refresh if cache is >12h old
    if (!empty($settings['ga4_property_id'])) {
        $stmt = $db->prepare("SELECT fetched_at FROM analytics_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = '30d'");
        $stmt->execute([$pid]);
        $lastFetch = $stmt->fetchColumn();

        if (!$lastFetch || strtotime($lastFetch) < time() - 43200) {
            try {
                foreach (['7d', '30d', '90d'] as $range) {
                    fetch_ga4_overview($pid, $range);
                    fetch_ga4_daily_visitors($pid, $range);
                    fetch_ga4_top_pages($pid, $range);
                    fetch_ga4_traffic_sources($pid, $range);
                }
                $projectResults['ga4'] = 'ok';
            } catch (Throwable $e) {
                $projectResults['ga4'] = 'error';
                error_log("Cron GA4 error for project $pid: " . $e->getMessage());
            }
        } else {
            $projectResults['ga4'] = 'fresh';
        }
    }

    // GSC: refresh if cache is >12h old
    if (!empty($settings['gsc_property'])) {
        $stmt = $db->prepare("SELECT fetched_at FROM search_console_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = '30d'");
        $stmt->execute([$pid]);
        $lastFetch = $stmt->fetchColumn();

        if (!$lastFetch || strtotime($lastFetch) < time() - 43200) {
            try {
                foreach (['7d', '30d', '90d'] as $range) {
                    fetch_gsc_overview($pid, $range);
                    fetch_gsc_keywords($pid, $range);
                    fetch_gsc_pages($pid, $range);
                }
                $projectResults['gsc'] = 'ok';
            } catch (Throwable $e) {
                $projectResults['gsc'] = 'error';
                error_log("Cron GSC error for project $pid: " . $e->getMessage());
            }
        } else {
            $projectResults['gsc'] = 'fresh';
        }
    }

    // PageSpeed: refresh weekly (>7 days old)
    $project = get_project($pid);
    $domain = $project['domain'] ?? '';
    if ($domain) {
        $stmt = $db->prepare("SELECT fetched_at FROM pagespeed_cache WHERE project_id = ? AND strategy = 'mobile' ORDER BY fetched_at DESC LIMIT 1");
        $stmt->execute([$pid]);
        $lastFetch = $stmt->fetchColumn();

        if (!$lastFetch || strtotime($lastFetch) < time() - 604800) {
            try {
                $url = str_starts_with($domain, 'http') ? $domain : 'https://' . $domain;
                fetch_pagespeed($pid, $url, 'mobile');
                fetch_pagespeed($pid, $url, 'desktop');
                $projectResults['pagespeed'] = 'ok';
            } catch (Throwable $e) {
                $projectResults['pagespeed'] = 'error';
                error_log("Cron PageSpeed error for project $pid: " . $e->getMessage());
            }
        } else {
            $projectResults['pagespeed'] = 'fresh';
        }
    }

    $results[] = $projectResults;
}

echo json_encode(['ok' => true, 'results' => $results]);
