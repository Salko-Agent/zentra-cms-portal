<?php
// admin/analytics/pagespeed.php — PageSpeed Insights Dashboard
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/analytics-helpers.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$settings   = get_settings($project_id);
$project    = get_project($project_id);
$strategy   = ($_GET['strategy'] ?? 'mobile') === 'desktop' ? 'desktop' : 'mobile';

// Default URL = project domain
$domain = $project['domain'] ?? ($settings['webhook_url'] ? preg_replace('#/_webhook\.php.*$#', '', $settings['webhook_url']) : '');
$testUrl = $_GET['url'] ?? $domain;

// Load cached data
$cached = null;
if ($testUrl) {
    $stmt = db()->prepare('SELECT scores_json, vitals_json, fetched_at FROM pagespeed_cache WHERE project_id = ? AND url = ? AND strategy = ?');
    $stmt->execute([$project_id, $testUrl, $strategy]);
    $row = $stmt->fetch();
    if ($row) {
        $cached = [
            'scores'     => json_decode($row['scores_json'], true),
            'vitals'     => json_decode($row['vitals_json'], true),
            'fetched_at' => $row['fetched_at'],
        ];
    }
}

$scores = $cached['scores'] ?? null;
$vitals = $cached['vitals'] ?? null;

$load_charts = true;
$page_title = 'PageSpeed';
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>PageSpeed Insights</h1>
    <p>Geschwindigkeit und Qualität deiner Website</p>
  </div>
</div>

<div class="url-input-bar">
  <input type="text" id="psUrl" value="<?= htmlspecialchars($testUrl) ?>" placeholder="deine-website.at" style="flex:1">
  <button class="btn btn-primary" onclick="runTest()" id="runTestBtn">Testen</button>
</div>

<div style="display:flex;gap:16px;align-items:center;margin-bottom:24px">
  <div class="strategy-tabs">
    <a href="?url=<?= urlencode($testUrl) ?>&strategy=mobile" class="tab <?= $strategy === 'mobile' ? 'active' : '' ?>">📱 Mobil</a>
    <a href="?url=<?= urlencode($testUrl) ?>&strategy=desktop" class="tab <?= $strategy === 'desktop' ? 'active' : '' ?>">🖥️ Desktop</a>
  </div>
  <?php if ($cached): ?>
  <span style="font-size:.82rem;color:var(--text-muted)">Zuletzt getestet: <?= date('d.m.Y H:i', strtotime($cached['fetched_at'])) ?></span>
  <?php endif; ?>
</div>

<?php if (!$scores): ?>
<div class="card">
  <div class="analytics-empty">
    <div class="empty-icon">⚡</div>
    <h3>Noch kein Test durchgeführt</h3>
    <p>Gib eine URL ein und klicke "Testen" um die PageSpeed-Analyse zu starten.</p>
  </div>
</div>
<?php else: ?>

<!-- Score Gauges -->
<div class="gauge-row">
  <div style="text-align:center">
    <canvas id="gaugePerf" width="120" height="120"></canvas>
    <div class="score-label">Performance</div>
  </div>
  <div style="text-align:center">
    <canvas id="gaugeA11y" width="120" height="120"></canvas>
    <div class="score-label">Barrierefreiheit</div>
  </div>
  <div style="text-align:center">
    <canvas id="gaugeBP" width="120" height="120"></canvas>
    <div class="score-label">Best Practices</div>
  </div>
  <div style="text-align:center">
    <canvas id="gaugeSEO" width="120" height="120"></canvas>
    <div class="score-label">SEO</div>
  </div>
</div>

<!-- Core Web Vitals -->
<h3 style="margin-bottom:16px">Core Web Vitals</h3>
<div class="vitals-row">
  <?= vitals_card('LCP', $vitals['lcp'] ?? '—', 's', rate_lcp($vitals['lcp'] ?? 99)) ?>
  <?= vitals_card('TBT', $vitals['tbt'] ?? '—', 'ms', rate_tbt($vitals['tbt'] ?? 999)) ?>
  <?= vitals_card('CLS', $vitals['cls'] ?? '—', '', rate_cls($vitals['cls'] ?? 1)) ?>
  <?= vitals_card('FCP', $vitals['fcp'] ?? '—', 's', rate_fcp($vitals['fcp'] ?? 99)) ?>
  <?= vitals_card('Speed Index', $vitals['si'] ?? '—', 's', rate_fcp($vitals['si'] ?? 99)) ?>
  <?= vitals_card('TTI', $vitals['tti'] ?? '—', 's', rate_fcp($vitals['tti'] ?? 99)) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  createGaugeChart('gaugePerf', <?= $scores['performance'] ?? 0 ?>, 'Performance');
  createGaugeChart('gaugeA11y', <?= $scores['accessibility'] ?? 0 ?>, 'A11Y');
  createGaugeChart('gaugeBP', <?= $scores['best_practices'] ?? 0 ?>, 'Best P.');
  createGaugeChart('gaugeSEO', <?= $scores['seo'] ?? 0 ?>, 'SEO');
});
</script>
<?php endif; ?>

<script>
function runTest() {
  const btn = document.getElementById('runTestBtn');
  btn.disabled = true;
  btn.textContent = 'Analysiert... (ca. 20s)';
  let url = document.getElementById('psUrl').value.trim();
  if (url && !url.match(/^https?:\/\//)) url = 'https://' + url;
  if (!url) { alert('Bitte URL eingeben'); btn.disabled = false; btn.textContent = 'Testen'; return; }
  const strategy = '<?= $strategy ?>';
  const csrf = document.querySelector('meta[name="csrf"]').content;
  fetch('/api/run-pagespeed.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ url, strategy }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      window.location.href = '?url=' + encodeURIComponent(url) + '&strategy=' + strategy;
    } else {
      alert(d.error || 'Fehler beim Test');
      btn.disabled = false;
      btn.textContent = 'Testen';
    }
  })
  .catch(e => { alert('Verbindungsfehler: ' + e.message); btn.disabled = false; btn.textContent = 'Testen'; });
}
</script>

<?php require_once __DIR__ . '/../../partials/admin-footer.php'; ?>
