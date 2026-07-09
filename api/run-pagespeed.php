<?php
// ============================================================
// Zentra API – POST /api/run-pagespeed.php
// Triggers a PageSpeed Insights test for a URL
// ============================================================

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics-fetchers.php';

header('Content-Type: application/json; charset=utf-8');

auth_check();
csrf_verify();

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?: $_POST;

$url = trim($body['url'] ?? '');
$strategy = ($body['strategy'] ?? 'mobile') === 'desktop' ? 'desktop' : 'mobile';

if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige URL']);
    exit;
}

$project_id = current_project_id();

try {
    $result = fetch_pagespeed($project_id, $url, $strategy);
    if ($result === null) {
        echo json_encode(['ok' => false, 'error' => 'PageSpeed-Test fehlgeschlagen. Prüfe die URL und den API-Key.']);
    } else {
        echo json_encode(['ok' => true, 'scores' => $result['scores']]);
    }
} catch (Throwable $e) {
    error_log('PageSpeed error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'PageSpeed-Test fehlgeschlagen. Bitte versuche es erneut.']);
}
