<?php
// ============================================================
// Zentra API – GET /api/test-google.php
// Tests Google API connection (GA4 and/or GSC)
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/google-auth.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$project_id = current_project_id();
$settings   = get_settings($project_id);
$results    = [];

// Test service account file exists
if (!file_exists(GOOGLE_SERVICE_ACCOUNT_JSON)) {
    echo json_encode(['ok' => false, 'error' => 'Service Account JSON nicht gefunden', 'results' => []]);
    exit;
}

// Test GA4
if (!empty($settings['ga4_property_id'])) {
    try {
        $token = google_get_access_token('https://www.googleapis.com/auth/analytics.readonly');
        $url = "https://analyticsdata.googleapis.com/v1beta/{$settings['ga4_property_id']}:runReport";
        $resp = google_api_post($url, [
            'dateRanges' => [['startDate' => 'yesterday', 'endDate' => 'yesterday']],
            'metrics'    => [['name' => 'activeUsers']],
        ], 'https://www.googleapis.com/auth/analytics.readonly');
        $results['ga4'] = empty($resp['error']) ? 'ok' : 'error';
    } catch (Throwable $e) {
        error_log('test-google GA4: ' . $e->getMessage());
        $results['ga4'] = 'error';
    }
} else {
    $results['ga4'] = 'not_configured';
}

// Test GSC
if (!empty($settings['gsc_property'])) {
    try {
        $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($settings['gsc_property']) . '/searchAnalytics/query';
        $resp = google_api_post($url, [
            'startDate' => date('Y-m-d', strtotime('-3 days')),
            'endDate'   => date('Y-m-d', strtotime('-1 day')),
        ], 'https://www.googleapis.com/auth/webmasters.readonly');
        if (empty($resp['error'])) {
            $results['gsc'] = 'ok';
        } else {
            $results['gsc'] = 'error';
            $results['gsc_detail'] = 'HTTP ' . ($resp['http_code'] ?? '?');
            $body = json_decode($resp['body'] ?? '', true);
            if (!empty($body['error']['message'])) {
                $results['gsc_detail'] .= ': ' . $body['error']['message'];
            }
            $results['gsc_property_used'] = $settings['gsc_property'];
        }
    } catch (Throwable $e) {
        error_log('test-google GSC: ' . $e->getMessage());
        $results['gsc'] = 'error';
        $results['gsc_detail'] = $e->getMessage();
    }
} else {
    $results['gsc'] = 'not_configured';
}

echo json_encode(['ok' => true, 'results' => $results]);
