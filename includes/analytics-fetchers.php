<?php
// ============================================================
// Zentra – Analytics Data Fetcher Functions
// GA4, Google Search Console, PageSpeed Insights, SEO Audit
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/google-auth.php';
require_once __DIR__ . '/functions.php';

// ── Scopes ──────────────────────────────────────────────────
define('SCOPE_GA4', 'https://www.googleapis.com/auth/analytics.readonly');
define('SCOPE_GSC', 'https://www.googleapis.com/auth/webmasters.readonly');

// ── Cache helpers ───────────────────────────────────────────

function save_analytics_cache(int $project_id, string $table, string $metric_type, string $date_range, array $data): void {
    $allowed_tables = ['analytics_cache', 'search_console_cache'];
    if (!in_array($table, $allowed_tables, true)) return;

    db()->prepare(
        "INSERT INTO `$table` (project_id, metric_type, date_range, data_json, fetched_at)
         VALUES (?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE data_json = VALUES(data_json), fetched_at = NOW()"
    )->execute([$project_id, $metric_type, $date_range, json_encode($data, JSON_UNESCAPED_UNICODE)]);
}

// ── Date range helper ───────────────────────────────────────

function analytics_date_range(string $range): array {
    $end = date('Y-m-d', strtotime('-1 day'));
    $map = ['7d' => 7, '30d' => 30, '90d' => 90];
    $days = $map[$range] ?? 30;
    $start = date('Y-m-d', strtotime("-{$days} days"));
    return [$start, $end];
}

// ══════════════════════════════════════════════════════════════
// GA4 DATA API
// ══════════════════════════════════════════════════════════════

function fetch_ga4_overview(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['ga4_property_id'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = "https://analyticsdata.googleapis.com/v1beta/{$property}:runReport";
    $body = [
        'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
        'metrics'    => [
            ['name' => 'activeUsers'],
            ['name' => 'sessions'],
            ['name' => 'screenPageViews'],
            ['name' => 'bounceRate'],
            ['name' => 'averageSessionDuration'],
        ],
    ];

    $resp = google_api_post($url, $body, SCOPE_GA4);
    if (!empty($resp['error'])) return null;

    $row = $resp['rows'][0]['metricValues'] ?? [];
    $data = [
        'active_users'    => (int)($row[0]['value'] ?? 0),
        'sessions'        => (int)($row[1]['value'] ?? 0),
        'page_views'      => (int)($row[2]['value'] ?? 0),
        'bounce_rate'     => round((float)($row[3]['value'] ?? 0) * 100, 1),
        'avg_session_dur' => round((float)($row[4]['value'] ?? 0), 0),
    ];

    save_analytics_cache($project_id, 'analytics_cache', 'overview', $date_range, $data);
    return $data;
}

function fetch_ga4_daily_visitors(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['ga4_property_id'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = "https://analyticsdata.googleapis.com/v1beta/{$property}:runReport";
    $body = [
        'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
        'dimensions' => [['name' => 'date']],
        'metrics'    => [['name' => 'activeUsers']],
        'orderBys'   => [['dimension' => ['dimensionName' => 'date']]],
    ];

    $resp = google_api_post($url, $body, SCOPE_GA4);
    if (!empty($resp['error'])) return null;

    $data = [];
    foreach ($resp['rows'] ?? [] as $row) {
        $dateStr = $row['dimensionValues'][0]['value'] ?? '';
        $data[] = [
            'date'    => substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2),
            'visitors' => (int)($row['metricValues'][0]['value'] ?? 0),
        ];
    }

    save_analytics_cache($project_id, 'analytics_cache', 'daily_visitors', $date_range, $data);
    return $data;
}

function fetch_ga4_top_pages(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['ga4_property_id'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = "https://analyticsdata.googleapis.com/v1beta/{$property}:runReport";
    $body = [
        'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
        'dimensions' => [['name' => 'pagePath']],
        'metrics'    => [['name' => 'screenPageViews'], ['name' => 'averageSessionDuration']],
        'orderBys'   => [['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]],
        'limit'      => 10,
    ];

    $resp = google_api_post($url, $body, SCOPE_GA4);
    if (!empty($resp['error'])) return null;

    $data = [];
    foreach ($resp['rows'] ?? [] as $row) {
        $data[] = [
            'page'       => $row['dimensionValues'][0]['value'] ?? '',
            'views'      => (int)($row['metricValues'][0]['value'] ?? 0),
            'avg_time'   => round((float)($row['metricValues'][1]['value'] ?? 0), 0),
        ];
    }

    save_analytics_cache($project_id, 'analytics_cache', 'top_pages', $date_range, $data);
    return $data;
}

function fetch_ga4_traffic_sources(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['ga4_property_id'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = "https://analyticsdata.googleapis.com/v1beta/{$property}:runReport";
    $body = [
        'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
        'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
        'metrics'    => [['name' => 'sessions']],
        'orderBys'   => [['metric' => ['metricName' => 'sessions'], 'desc' => true]],
        'limit'      => 8,
    ];

    $resp = google_api_post($url, $body, SCOPE_GA4);
    if (!empty($resp['error'])) return null;

    $data = [];
    foreach ($resp['rows'] ?? [] as $row) {
        $data[] = [
            'channel'  => $row['dimensionValues'][0]['value'] ?? '',
            'sessions' => (int)($row['metricValues'][0]['value'] ?? 0),
        ];
    }

    save_analytics_cache($project_id, 'analytics_cache', 'traffic_sources', $date_range, $data);
    return $data;
}

// ══════════════════════════════════════════════════════════════
// GOOGLE SEARCH CONSOLE API
// ══════════════════════════════════════════════════════════════

function fetch_gsc_overview(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['gsc_property'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($property) . '/searchAnalytics/query';
    $body = [
        'startDate' => $startDate,
        'endDate'   => $endDate,
    ];

    $resp = google_api_post($url, $body, SCOPE_GSC);
    if (!empty($resp['error'])) return null;

    $rows = $resp['rows'] ?? [];
    $data = [
        'clicks'      => 0,
        'impressions'  => 0,
        'ctr'         => 0,
        'position'    => 0,
    ];
    foreach ($rows as $row) {
        $data['clicks']     += $row['clicks'] ?? 0;
        $data['impressions'] += $row['impressions'] ?? 0;
    }
    if ($data['impressions'] > 0) {
        $data['ctr'] = round($data['clicks'] / $data['impressions'] * 100, 2);
    }
    if (!empty($rows)) {
        $totalPos = 0;
        foreach ($rows as $row) $totalPos += ($row['position'] ?? 0);
        $data['position'] = round($totalPos / count($rows), 1);
    }

    save_analytics_cache($project_id, 'search_console_cache', 'overview', $date_range, $data);
    return $data;
}

function fetch_gsc_keywords(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['gsc_property'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($property) . '/searchAnalytics/query';
    $body = [
        'startDate'  => $startDate,
        'endDate'    => $endDate,
        'dimensions' => ['query'],
        'rowLimit'   => 50,
    ];

    $resp = google_api_post($url, $body, SCOPE_GSC);
    if (!empty($resp['error'])) return null;

    $data = [];
    foreach ($resp['rows'] ?? [] as $row) {
        $data[] = [
            'keyword'     => $row['keys'][0] ?? '',
            'clicks'      => $row['clicks'] ?? 0,
            'impressions' => $row['impressions'] ?? 0,
            'ctr'         => round(($row['ctr'] ?? 0) * 100, 2),
            'position'    => round($row['position'] ?? 0, 1),
        ];
    }

    save_analytics_cache($project_id, 'search_console_cache', 'keywords', $date_range, $data);
    return $data;
}

function fetch_gsc_pages(int $project_id, string $date_range = '30d'): ?array {
    $settings = get_settings($project_id);
    $property = $settings['gsc_property'] ?? '';
    if (!$property) return null;

    [$startDate, $endDate] = analytics_date_range($date_range);

    $url = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($property) . '/searchAnalytics/query';
    $body = [
        'startDate'  => $startDate,
        'endDate'    => $endDate,
        'dimensions' => ['page'],
        'rowLimit'   => 30,
    ];

    $resp = google_api_post($url, $body, SCOPE_GSC);
    if (!empty($resp['error'])) return null;

    $data = [];
    foreach ($resp['rows'] ?? [] as $row) {
        $data[] = [
            'page'        => $row['keys'][0] ?? '',
            'clicks'      => $row['clicks'] ?? 0,
            'impressions' => $row['impressions'] ?? 0,
            'ctr'         => round(($row['ctr'] ?? 0) * 100, 2),
            'position'    => round($row['position'] ?? 0, 1),
        ];
    }

    save_analytics_cache($project_id, 'search_console_cache', 'pages', $date_range, $data);
    return $data;
}

// ══════════════════════════════════════════════════════════════
// PAGESPEED INSIGHTS API
// ══════════════════════════════════════════════════════════════

function fetch_pagespeed(int $project_id, string $url, string $strategy = 'mobile'): ?array {
    $apiKey = defined('PAGESPEED_API_KEY') ? PAGESPEED_API_KEY : '';
    // Per-project override
    $settings = get_settings($project_id);
    if (!empty($settings['pagespeed_api_key'])) {
        $apiKey = $settings['pagespeed_api_key'];
    }

    $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?'
        . http_build_query([
            'url'      => $url,
            'strategy' => $strategy,
            'key'      => $apiKey,
            'category' => ['PERFORMANCE', 'ACCESSIBILITY', 'BEST_PRACTICES', 'SEO'],
        ]);

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("PageSpeed API failed: HTTP $httpCode | " . substr($resp, 0, 500));
        return null;
    }

    $data = json_decode($resp, true);
    if (empty($data['lighthouseResult'])) return null;

    $lh = $data['lighthouseResult'];
    $cats = $lh['categories'] ?? [];
    $audits = $lh['audits'] ?? [];

    $scores = [
        'performance'    => (int)(($cats['performance']['score'] ?? 0) * 100),
        'accessibility'  => (int)(($cats['accessibility']['score'] ?? 0) * 100),
        'best_practices' => (int)(($cats['best-practices']['score'] ?? 0) * 100),
        'seo'            => (int)(($cats['seo']['score'] ?? 0) * 100),
    ];

    $vitals = [
        'lcp'  => round(($audits['largest-contentful-paint']['numericValue'] ?? 0) / 1000, 2),
        'tbt'  => round($audits['total-blocking-time']['numericValue'] ?? 0, 0),
        'cls'  => round($audits['cumulative-layout-shift']['numericValue'] ?? 0, 3),
        'fcp'  => round(($audits['first-contentful-paint']['numericValue'] ?? 0) / 1000, 2),
        'si'   => round(($audits['speed-index']['numericValue'] ?? 0) / 1000, 2),
        'tti'  => round(($audits['interactive']['numericValue'] ?? 0) / 1000, 2),
    ];

    // Save to cache
    db()->prepare(
        'INSERT INTO pagespeed_cache (project_id, url, strategy, scores_json, vitals_json, fetched_at)
         VALUES (?, ?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE scores_json = VALUES(scores_json), vitals_json = VALUES(vitals_json), fetched_at = NOW()'
    )->execute([$project_id, $url, $strategy, json_encode($scores), json_encode($vitals)]);

    return ['scores' => $scores, 'vitals' => $vitals];
}

// ══════════════════════════════════════════════════════════════
// ON-PAGE SEO AUDIT (DOMDocument)
// ══════════════════════════════════════════════════════════════

function run_seo_audit(int $project_id, string $url): ?array {
    // SSRF protection: only allow the project's domain
    $settings = get_settings($project_id);
    $project  = get_project($project_id);
    $domain   = $project['domain'] ?? '';

    // Reject if no domain is configured
    if (!$domain) {
        return null;
    }

    $urlHost = parse_url($url, PHP_URL_HOST);
    $domainHost = parse_url($domain, PHP_URL_HOST) ?: parse_url('https://' . $domain, PHP_URL_HOST);
    if (!$urlHost || !$domainHost || $urlHost !== $domainHost) {
        return null;
    }

    // Block internal/private IPs
    $ip = gethostbyname($urlHost);
    if ($ip && preg_match('/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|0\.|169\.254\.|::1|localhost)/i', $ip)) {
        return null;
    }

    // Fetch the page
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_USERAGENT      => 'ZentraSEOAudit/1.0',
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
    ]);
    $html     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size     = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        return ['score' => 0, 'issues' => [['rule' => 'page_load', 'severity' => 'critical', 'message' => "Seite konnte nicht geladen werden (HTTP $httpCode)"]]];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);

    $issues = [];
    $points = 0;
    $maxPoints = 20;

    // 1. Title
    $titles = $dom->getElementsByTagName('title');
    $titleText = $titles->length > 0 ? trim($titles->item(0)->textContent) : '';
    if (!$titleText) {
        $issues[] = ['rule' => 'title', 'severity' => 'critical', 'message' => 'Kein <title>-Tag gefunden'];
    } elseif (mb_strlen($titleText) < 30 || mb_strlen($titleText) > 60) {
        $issues[] = ['rule' => 'title_length', 'severity' => 'warning', 'message' => "Title hat " . mb_strlen($titleText) . " Zeichen (optimal: 30-60)"];
    } else {
        $points++;
    }

    // 2. Meta Description
    $metaDesc = '';
    $metas = $dom->getElementsByTagName('meta');
    foreach ($metas as $m) {
        if (strtolower($m->getAttribute('name')) === 'description') {
            $metaDesc = $m->getAttribute('content');
            break;
        }
    }
    if (!$metaDesc) {
        $issues[] = ['rule' => 'meta_description', 'severity' => 'critical', 'message' => 'Keine Meta-Description gefunden'];
    } elseif (mb_strlen($metaDesc) < 120 || mb_strlen($metaDesc) > 160) {
        $issues[] = ['rule' => 'meta_desc_length', 'severity' => 'warning', 'message' => "Meta-Description hat " . mb_strlen($metaDesc) . " Zeichen (optimal: 120-160)"];
    } else {
        $points++;
    }

    // 3. H1 count
    $h1s = $dom->getElementsByTagName('h1');
    if ($h1s->length === 0) {
        $issues[] = ['rule' => 'h1_missing', 'severity' => 'critical', 'message' => 'Kein H1-Tag gefunden'];
    } elseif ($h1s->length > 1) {
        $issues[] = ['rule' => 'h1_multiple', 'severity' => 'warning', 'message' => $h1s->length . ' H1-Tags gefunden (sollte genau 1 sein)'];
    } else {
        $points++;
    }

    // 4. H1 contains keyword from title
    if ($h1s->length > 0 && $titleText) {
        $h1Text = trim($h1s->item(0)->textContent);
        $titleWords = array_filter(explode(' ', mb_strtolower($titleText)), fn($w) => mb_strlen($w) > 3);
        $h1Lower = mb_strtolower($h1Text);
        $match = false;
        foreach ($titleWords as $word) {
            if (str_contains($h1Lower, $word)) { $match = true; break; }
        }
        if ($match) {
            $points++;
        } else {
            $issues[] = ['rule' => 'h1_keyword', 'severity' => 'warning', 'message' => 'H1 enthält keine Keywords aus dem Title'];
        }
    }

    // 5. Heading hierarchy
    $headings = [];
    for ($i = 1; $i <= 6; $i++) {
        foreach ($dom->getElementsByTagName("h$i") as $h) {
            $headings[] = $i;
        }
    }
    $hierarchyOk = true;
    $prevLevel = 0;
    foreach ($headings as $level) {
        if ($level > $prevLevel + 1 && $prevLevel > 0) {
            $hierarchyOk = false;
            break;
        }
        $prevLevel = $level;
    }
    if ($hierarchyOk) {
        $points++;
    } else {
        $issues[] = ['rule' => 'heading_hierarchy', 'severity' => 'warning', 'message' => 'Heading-Hierarchie ist nicht korrekt (z.B. H3 vor H2)'];
    }

    // 6. Images alt tags
    $imgs = $dom->getElementsByTagName('img');
    $imgNoAlt = 0;
    foreach ($imgs as $img) {
        if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
            $imgNoAlt++;
        }
    }
    if ($imgNoAlt === 0) {
        $points++;
    } else {
        $issues[] = ['rule' => 'img_alt', 'severity' => 'warning', 'message' => "$imgNoAlt Bild(er) ohne alt-Attribut"];
    }

    // 7. Open Graph tags
    $ogTags = ['og:title' => false, 'og:description' => false, 'og:image' => false];
    foreach ($metas as $m) {
        $prop = $m->getAttribute('property');
        if (isset($ogTags[$prop]) && $m->getAttribute('content')) {
            $ogTags[$prop] = true;
        }
    }
    $ogMissing = array_keys(array_filter($ogTags, fn($v) => !$v));
    if (empty($ogMissing)) {
        $points++;
    } else {
        $issues[] = ['rule' => 'og_tags', 'severity' => 'warning', 'message' => 'Fehlende OG-Tags: ' . implode(', ', $ogMissing)];
    }

    // 8. Canonical URL
    $canonical = false;
    foreach ($dom->getElementsByTagName('link') as $link) {
        if ($link->getAttribute('rel') === 'canonical' && $link->getAttribute('href')) {
            $canonical = true;
            break;
        }
    }
    if ($canonical) { $points++; } else {
        $issues[] = ['rule' => 'canonical', 'severity' => 'warning', 'message' => 'Kein Canonical-Link gefunden'];
    }

    // 9. Language attribute
    $html_el = $dom->getElementsByTagName('html');
    $lang = $html_el->length > 0 ? $html_el->item(0)->getAttribute('lang') : '';
    if ($lang) { $points++; } else {
        $issues[] = ['rule' => 'lang_attr', 'severity' => 'warning', 'message' => 'Kein lang-Attribut auf <html>'];
    }

    // 10. Viewport meta
    $viewport = false;
    foreach ($metas as $m) {
        if (strtolower($m->getAttribute('name')) === 'viewport') {
            $viewport = true;
            break;
        }
    }
    if ($viewport) { $points++; } else {
        $issues[] = ['rule' => 'viewport', 'severity' => 'critical', 'message' => 'Kein Viewport-Meta-Tag gefunden'];
    }

    // 11. Text-to-HTML ratio
    $textContent = trim($dom->getElementsByTagName('body')->item(0)?->textContent ?? '');
    $textLen = mb_strlen($textContent);
    $htmlLen = mb_strlen($html);
    $ratio = $htmlLen > 0 ? ($textLen / $htmlLen) * 100 : 0;
    if ($ratio >= 10) { $points++; } else {
        $issues[] = ['rule' => 'text_ratio', 'severity' => 'warning', 'message' => "Text-zu-HTML Ratio: " . round($ratio, 1) . "% (sollte >10% sein)"];
    }

    // 12. Title != H1
    if ($titleText && $h1s->length > 0) {
        $h1Text = trim($h1s->item(0)->textContent);
        if ($titleText !== $h1Text) { $points++; } else {
            $issues[] = ['rule' => 'title_h1_duplicate', 'severity' => 'info', 'message' => 'Title und H1 sind identisch'];
        }
    } else { $points++; }

    // 13. Robots meta not blocking
    $robotsBlocking = false;
    foreach ($metas as $m) {
        if (strtolower($m->getAttribute('name')) === 'robots') {
            $content = strtolower($m->getAttribute('content'));
            if (str_contains($content, 'noindex')) {
                $robotsBlocking = true;
            }
        }
    }
    if (!$robotsBlocking) { $points++; } else {
        $issues[] = ['rule' => 'robots_noindex', 'severity' => 'critical', 'message' => 'robots meta blockiert Indexierung (noindex)'];
    }

    // 14. Structured data (JSON-LD)
    $jsonLd = $xpath->query('//script[@type="application/ld+json"]');
    if ($jsonLd->length > 0) { $points++; } else {
        $issues[] = ['rule' => 'structured_data', 'severity' => 'warning', 'message' => 'Keine strukturierten Daten (JSON-LD) gefunden'];
    }

    // 15. HTTPS
    if (str_starts_with($finalUrl, 'https://')) { $points++; } else {
        $issues[] = ['rule' => 'https', 'severity' => 'critical', 'message' => 'Seite verwendet kein HTTPS'];
    }

    // 16. Page size
    if ($size < 3 * 1024 * 1024) { $points++; } else {
        $issues[] = ['rule' => 'page_size', 'severity' => 'warning', 'message' => 'Seitengröße: ' . round($size / 1024 / 1024, 1) . 'MB (sollte <3MB sein)'];
    }

    // 17. Favicon
    $favicon = false;
    foreach ($dom->getElementsByTagName('link') as $link) {
        $rel = strtolower($link->getAttribute('rel'));
        if (str_contains($rel, 'icon') && $link->getAttribute('href')) {
            $favicon = true;
            break;
        }
    }
    if ($favicon) { $points++; } else {
        $issues[] = ['rule' => 'favicon', 'severity' => 'info', 'message' => 'Kein Favicon-Link gefunden'];
    }

    // 18. hreflang / lang correct
    if ($lang && preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $lang)) { $points++; } else if ($lang) {
        $issues[] = ['rule' => 'lang_format', 'severity' => 'info', 'message' => "lang-Attribut Format ungültig: '$lang'"];
    } else { /* already flagged in rule 9 */ }

    // 19. Empty links
    $emptyLinks = 0;
    foreach ($dom->getElementsByTagName('a') as $a) {
        $href = trim($a->getAttribute('href'));
        if ($href === '' || $href === '#') $emptyLinks++;
    }
    if ($emptyLinks === 0) { $points++; } else {
        $issues[] = ['rule' => 'empty_links', 'severity' => 'info', 'message' => "$emptyLinks leere Links (href='' oder href='#')"];
    }

    // 20. Duplicate meta tags
    $metaCounts = [];
    foreach ($metas as $m) {
        $name = strtolower($m->getAttribute('name') ?: $m->getAttribute('property'));
        if ($name) $metaCounts[$name] = ($metaCounts[$name] ?? 0) + 1;
    }
    $duplicates = array_filter($metaCounts, fn($c) => $c > 1);
    if (empty($duplicates)) { $points++; } else {
        $issues[] = ['rule' => 'duplicate_meta', 'severity' => 'warning', 'message' => 'Doppelte Meta-Tags: ' . implode(', ', array_keys($duplicates))];
    }

    $score = (int)round(($points / $maxPoints) * 100);

    // Save to cache
    db()->prepare(
        'INSERT INTO seo_audit_cache (project_id, url, score, issues_json, audited_at)
         VALUES (?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE score = VALUES(score), issues_json = VALUES(issues_json), audited_at = NOW()'
    )->execute([$project_id, $url, $score, json_encode($issues, JSON_UNESCAPED_UNICODE)]);

    return ['score' => $score, 'issues' => $issues];
}
