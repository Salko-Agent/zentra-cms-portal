<?php
// admin/analytics/search-console.php — Google Search Console Details
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

$hasGSC = !empty($settings['gsc_property']);

// Load cached data
$gsc = null;
$keywords = [];
$pages = [];

if ($hasGSC) {
    $stmt = db()->prepare("SELECT data_json FROM search_console_cache WHERE project_id = ? AND metric_type = 'overview' AND date_range = ?");
    $stmt->execute([$project_id, $range]);
    $row = $stmt->fetch();
    $gsc = $row ? json_decode($row['data_json'], true) : null;

    $kwStmt = db()->prepare("SELECT data_json FROM search_console_cache WHERE project_id = ? AND metric_type = 'keywords' AND date_range = ?");
    $kwStmt->execute([$project_id, $range]);
    $kwRow = $kwStmt->fetch();
    $keywords = $kwRow ? json_decode($kwRow['data_json'], true) : [];

    $pgStmt = db()->prepare("SELECT data_json FROM search_console_cache WHERE project_id = ? AND metric_type = 'pages' AND date_range = ?");
    $pgStmt->execute([$project_id, $range]);
    $pgRow = $pgStmt->fetch();
    $pages = $pgRow ? json_decode($pgRow['data_json'], true) : [];
}

$load_charts = true;
$page_title = 'Search Console';
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Google Search Console</h1>
    <p>Keyword-Rankings und Suchperformance</p>
  </div>
  <div class="date-range-tabs">
    <a href="?range=7d" class="tab <?= $range === '7d' ? 'active' : '' ?>">7 Tage</a>
    <a href="?range=30d" class="tab <?= $range === '30d' ? 'active' : '' ?>">30 Tage</a>
    <a href="?range=90d" class="tab <?= $range === '90d' ? 'active' : '' ?>">90 Tage</a>
  </div>
</div>

<?php if (!$hasGSC): ?>
<div class="card">
  <div class="analytics-empty">
    <div class="empty-icon">🔎</div>
    <h3>Search Console nicht konfiguriert</h3>
    <p>Füge deine Search Console Property in den Einstellungen hinzu und lade den Service Account als Benutzer ein.</p>
    <a href="/admin/settings.php" class="btn btn-primary">Einstellungen öffnen</a>
  </div>
</div>
<?php elseif (!$gsc): ?>
<div class="card">
  <div class="analytics-empty">
    <div class="empty-icon">⏳</div>
    <h3>Noch keine Daten</h3>
    <p>Die Daten werden automatisch abgerufen. Klicke auf der Übersichtsseite auf "Aktualisieren" oder warte auf den nächsten Cron-Lauf.</p>
  </div>
</div>
<?php else: ?>

<!-- Overview Cards -->
<div class="stats-row">
  <div class="stat-card-analytics">
    <div class="stat-value"><?= number_format($gsc['clicks']) ?></div>
    <div class="stat-label">Klicks</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= number_format($gsc['impressions']) ?></div>
    <div class="stat-label">Impressionen</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $gsc['ctr'] ?>%</div>
    <div class="stat-label">CTR</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value"><?= $gsc['position'] ?></div>
    <div class="stat-label">Ø Position</div>
  </div>
</div>

<!-- Keywords Table -->
<?php if (!empty($keywords)): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Top Keywords (<?= count($keywords) ?>)</div>
  <table class="keywords-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Keyword</th>
        <th>Klicks</th>
        <th>Impressionen</th>
        <th>CTR</th>
        <th>Position</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($keywords as $i => $kw): ?>
      <tr>
        <td data-label="#" style="color:var(--text-muted)"><?= $i + 1 ?></td>
        <td data-label="Keyword"><strong><?= htmlspecialchars($kw['keyword']) ?></strong></td>
        <td data-label="Klicks"><?= number_format($kw['clicks']) ?></td>
        <td data-label="Impressionen"><?= number_format($kw['impressions']) ?></td>
        <td data-label="CTR"><?= $kw['ctr'] ?>%</td>
        <td data-label="Position">
          <?php
          $posColor = $kw['position'] <= 3 ? 'var(--green)' : ($kw['position'] <= 10 ? 'var(--gold)' : ($kw['position'] <= 20 ? 'var(--orange)' : 'var(--text-muted)'));
          ?>
          <span style="color:<?= $posColor ?>;font-weight:600"><?= $kw['position'] ?></span>
          <span class="position-bar" style="width:<?= max(4, min(60, (50 - $kw['position']) * 2)) ?>px;background:<?= $posColor ?>"></span>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Pages Table -->
<?php if (!empty($pages)): ?>
<div class="card">
  <div class="card-title">Top Seiten (<?= count($pages) ?>)</div>
  <table class="keywords-table">
    <thead>
      <tr>
        <th>Seite</th>
        <th>Klicks</th>
        <th>Impressionen</th>
        <th>CTR</th>
        <th>Position</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pages as $pg): ?>
      <tr>
        <td data-label="Seite" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($pg['page']) ?>">
          <?= htmlspecialchars($pg['page']) ?>
        </td>
        <td data-label="Klicks"><?= number_format($pg['clicks']) ?></td>
        <td data-label="Impressionen"><?= number_format($pg['impressions']) ?></td>
        <td data-label="CTR"><?= $pg['ctr'] ?>%</td>
        <td data-label="Position"><?= $pg['position'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../partials/admin-footer.php'; ?>
