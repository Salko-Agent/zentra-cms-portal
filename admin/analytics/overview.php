<?php
// admin/analytics/overview.php — Main Analytics Dashboard
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/analytics-helpers.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$settings   = get_settings($project_id);
$range      = $_GET['range'] ?? '30d';
if (!in_array($range, ['7d', '30d', '90d'])) $range = '30d';

// Load cached data
$db = db();

// GA4 overview
$gaStmt = $db->prepare("SELECT data_json, fetched_at FROM analytics_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = ?");
$gaStmt->execute([$project_id, $range]);
$gaRow = $gaStmt->fetch();
$ga = $gaRow ? json_decode($gaRow['data_json'], true) : null;

// GA4 daily visitors
$dvStmt = $db->prepare("SELECT data_json FROM analytics_cache WHERE project_id = ? AND metric_type = 'daily_visitors' AND date_range = ?");
$dvStmt->execute([$project_id, $range]);
$dvRow = $dvStmt->fetch();
$dailyVisitors = $dvRow ? json_decode($dvRow['data_json'], true) : [];

// GA4 top pages
$tpStmt = $db->prepare("SELECT data_json FROM analytics_cache WHERE project_id = ? AND metric_type = 'top_pages' AND date_range = ?");
$tpStmt->execute([$project_id, $range]);
$tpRow = $tpStmt->fetch();
$topPages = $tpRow ? json_decode($tpRow['data_json'], true) : [];

// GA4 traffic sources
$tsStmt = $db->prepare("SELECT data_json FROM analytics_cache WHERE project_id = ? AND metric_type = 'traffic_sources' AND date_range = ?");
$tsStmt->execute([$project_id, $range]);
$tsRow = $tsStmt->fetch();
$trafficSources = $tsRow ? json_decode($tsRow['data_json'], true) : [];

// GSC overview
$gscStmt = $db->prepare("SELECT data_json FROM search_console_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = ?");
$gscStmt->execute([$project_id, $range]);
$gscRow = $gscStmt->fetch();
$gsc = $gscRow ? json_decode($gscRow['data_json'], true) : null;

// GSC keywords
$kwStmt = $db->prepare("SELECT data_json FROM search_console_cache WHERE project_id = ? AND metric_type = 'keywords' AND date_range = ?");
$kwStmt->execute([$project_id, $range]);
$kwRow = $kwStmt->fetch();
$keywords = $kwRow ? json_decode($kwRow['data_json'], true) : [];

// PageSpeed (latest mobile)
$psStmt = $db->prepare("SELECT scores_json FROM pagespeed_cache WHERE project_id = ? AND strategy = 'mobile' ORDER BY fetched_at DESC LIMIT 1");
$psStmt->execute([$project_id]);
$psRow = $psStmt->fetch();
$psScores = $psRow ? json_decode($psRow['scores_json'], true) : null;

// Recent activity (super-admin only)
$recentActivity = [];
if (!empty($_SESSION['is_super_admin'])) {
    $actStmt = $db->prepare("SELECT al.*, u.name as user_name FROM activity_log al LEFT JOIN users u ON u.id = al.user_id WHERE al.project_id = ? ORDER BY al.created_at DESC LIMIT 10");
    $actStmt->execute([$project_id]);
    $recentActivity = $actStmt->fetchAll();
}

// Last update time
$lastUpdate = $gaRow['fetched_at'] ?? null;

$hasGA4 = !empty($settings['ga4_property_id']);
$hasGSC = !empty($settings['gsc_property']);
$hasAnyData = $ga || $gsc || $psScores;

$load_charts = true;
$page_title = 'Analytics Übersicht';
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Analytics Übersicht</h1>
    <p>Leistungsdaten deiner Website</p>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <div class="date-range-tabs">
      <a href="?range=7d" class="tab <?= $range === '7d' ? 'active' : '' ?>">7 Tage</a>
      <a href="?range=30d" class="tab <?= $range === '30d' ? 'active' : '' ?>">30 Tage</a>
      <a href="?range=90d" class="tab <?= $range === '90d' ? 'active' : '' ?>">90 Tage</a>
    </div>
    <button class="btn btn-secondary btn-sm" id="refreshBtn" onclick="refreshData()">↻ Aktualisieren</button>
  </div>
</div>

<?php if ($lastUpdate): ?>
<div class="refresh-bar">
  <span class="last-update">Zuletzt aktualisiert: <?= date('d.m.Y H:i', strtotime($lastUpdate)) ?></span>
</div>
<?php endif; ?>

<?php if (!$hasAnyData && !$hasGA4 && !$hasGSC): ?>
<div class="card">
  <div class="analytics-empty">
    <div class="empty-icon">📊</div>
    <h3>Analytics noch nicht eingerichtet</h3>
    <p>Konfiguriere deine Google Analytics und Search Console Verbindung in den Einstellungen, um hier Daten zu sehen.</p>
    <a href="/admin/settings.php" class="btn btn-primary">Einstellungen öffnen</a>
  </div>
</div>
<?php else: ?>

<!-- Stat Cards -->
<div class="stats-row">
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $ga ? number_format($ga['active_users']) : '—' ?></div>
    <div class="stat-label">Besucher</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $ga ? number_format($ga['sessions']) : '—' ?></div>
    <div class="stat-label">Sessions</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $ga ? number_format($ga['page_views']) : '—' ?></div>
    <div class="stat-label">Seitenaufrufe</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $ga ? $ga['bounce_rate'] . '%' : '—' ?></div>
    <div class="stat-label">Absprungrate</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $gsc ? $gsc['position'] : '—' ?></div>
    <div class="stat-label">Ø Position</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $psScores ? $psScores['performance'] : '—' ?></div>
    <div class="stat-label">PageSpeed</div>
  </div>
</div>

<!-- Daily Visitors Chart -->
<?php if (!empty($dailyVisitors)): ?>
<div class="chart-container">
  <div class="chart-title">Tägliche Besucher</div>
  <div class="chart-wrap">
    <canvas id="dailyVisitorsChart"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- Two columns: Top Pages + Traffic Sources -->
<div class="analytics-grid">
  <?php if (!empty($topPages)): ?>
  <div class="chart-container">
    <div class="chart-title">Top Seiten</div>
    <div class="chart-wrap">
      <canvas id="topPagesChart"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($trafficSources)): ?>
  <div class="chart-container">
    <div class="chart-title">Traffic-Quellen</div>
    <div class="chart-wrap chart-wrap-sm">
      <canvas id="trafficSourcesChart"></canvas>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Two columns: Keywords + Recent Activity -->
<div class="analytics-grid">
  <?php if (!empty($keywords)): ?>
  <div class="chart-container">
    <div class="chart-title">Top Keywords (Search Console)</div>
    <table class="keywords-table">
      <thead>
        <tr><th>Keyword</th><th>Klicks</th><th>Impr.</th><th>CTR</th><th>Position</th></tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($keywords, 0, 10) as $kw): ?>
        <tr>
          <td data-label="Keyword"><?= htmlspecialchars($kw['keyword']) ?></td>
          <td data-label="Klicks"><?= number_format($kw['clicks']) ?></td>
          <td data-label="Impr."><?= number_format($kw['impressions']) ?></td>
          <td data-label="CTR"><?= $kw['ctr'] ?>%</td>
          <td data-label="Position">
            <span class="position-bar" style="width:<?= max(4, min(60, (50 - $kw['position']) * 2)) ?>px"></span>
            <?= $kw['position'] ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (count($keywords) > 10): ?>
    <div style="padding:10px;text-align:center">
      <a href="/admin/analytics/search-console.php?range=<?= $range ?>" class="btn btn-secondary btn-sm">Alle Keywords →</a>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['is_super_admin'])): ?>
  <div class="chart-container">
    <div class="chart-title">Letzte Aktivitäten</div>
    <?php if (!empty($recentActivity)): ?>
    <table class="activity-table">
      <tbody>
        <?php foreach ($recentActivity as $act): ?>
        <tr>
          <td data-label="Aktion"><?= activity_action_badge($act['action']) ?></td>
          <td data-label="Benutzer"><?= htmlspecialchars($act['user_name'] ?? 'System') ?></td>
          <td data-label="Zeit" class="activity-time"><?= time_ago($act['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="padding:10px;text-align:center">
      <a href="/admin/analytics/activity-log.php" class="btn btn-secondary btn-sm">Alle Aktivitäten →</a>
    </div>
    <?php else: ?>
    <p style="color:var(--text-muted);text-align:center;padding:20px">Noch keine Aktivitäten</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

<?php
// Super admin: cross-project comparison
if (!empty($_SESSION['is_super_admin'])):
    $allProjects = get_all_projects();
?>
<div class="card" style="margin-top:32px">
  <div class="card-title">Alle Projekte (Admin)</div>
  <table class="data-table">
    <thead>
      <tr><th>Projekt</th><th>Besucher (30d)</th><th>PageSpeed</th><th>Letzte Aktivität</th></tr>
    </thead>
    <tbody>
      <?php foreach ($allProjects as $p):
        $pGa = $db->prepare("SELECT data_json FROM analytics_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = '30d'");
        $pGa->execute([$p['id']]);
        $pGaData = $pGa->fetch();
        $pData = $pGaData ? json_decode($pGaData['data_json'], true) : null;
        $pPs = $db->prepare("SELECT scores_json FROM pagespeed_cache WHERE project_id = ? AND strategy = 'mobile' ORDER BY fetched_at DESC LIMIT 1");
        $pPs->execute([$p['id']]);
        $pPsData = $pPs->fetch();
        $pScores = $pPsData ? json_decode($pPsData['scores_json'], true) : null;
        $pAct = $db->prepare("SELECT created_at FROM activity_log WHERE project_id = ? ORDER BY created_at DESC LIMIT 1");
        $pAct->execute([$p['id']]);
        $lastAct = $pAct->fetchColumn();
      ?>
      <tr>
        <td data-label="Projekt"><a href="/admin/switch-project.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></td>
        <td data-label="Besucher (30d)"><?= $pData ? number_format($pData['active_users']) : '—' ?></td>
        <td data-label="PageSpeed"><?= $pScores ? $pScores['performance'] : '—' ?></td>
        <td data-label="Letzte Aktivität" class="activity-time"><?= $lastAct ? time_ago($lastAct) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Daily visitors chart
  <?php if (!empty($dailyVisitors)): ?>
  const dvLabels = <?= json_encode(array_map(fn($d) => $d['date'], $dailyVisitors)) ?>;
  const dvData = <?= json_encode(array_map(fn($d) => $d['visitors'], $dailyVisitors)) ?>;
  createLineChart('dailyVisitorsChart', dvLabels.map(formatDateLabel), [
    { label: 'Besucher', data: dvData }
  ]);
  <?php endif; ?>

  // Top pages chart
  <?php if (!empty($topPages)): ?>
  const tpLabels = <?= json_encode(array_map(fn($p) => $p['page'], $topPages)) ?>;
  const tpData = <?= json_encode(array_map(fn($p) => $p['views'], $topPages)) ?>;
  createBarChart('topPagesChart', tpLabels, tpData);
  <?php endif; ?>

  // Traffic sources chart
  <?php if (!empty($trafficSources)): ?>
  const tsLabels = <?= json_encode(array_map(fn($s) => $s['channel'], $trafficSources)) ?>;
  const tsData = <?= json_encode(array_map(fn($s) => $s['sessions'], $trafficSources)) ?>;
  createDoughnutChart('trafficSourcesChart', tsLabels, tsData);
  <?php endif; ?>
});

function refreshData() {
  const btn = document.getElementById('refreshBtn');
  btn.disabled = true;
  btn.textContent = '↻ Lädt...';
  fetch('/api/refresh-analytics.php', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf"]').content },
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) location.reload();
    else { alert(d.error || 'Fehler beim Aktualisieren'); btn.disabled = false; btn.textContent = '↻ Aktualisieren'; }
  })
  .catch(() => { btn.disabled = false; btn.textContent = '↻ Aktualisieren'; });
}
</script>

<?php require_once __DIR__ . '/../../partials/admin-footer.php'; ?>
