<?php
// admin/analytics/seo-audit.php — On-Page SEO Checker
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

// Default URL
$domain = $project['domain'] ?? ($settings['webhook_url'] ? preg_replace('#/_webhook\.php.*$#', '', $settings['webhook_url']) : '');
$testUrl = $_GET['url'] ?? $domain;

// Load cached audit
$cached = null;
if ($testUrl) {
    $stmt = db()->prepare('SELECT score, issues_json, audited_at FROM seo_audit_cache WHERE project_id = ? AND url = ?');
    $stmt->execute([$project_id, $testUrl]);
    $row = $stmt->fetch();
    if ($row) {
        $cached = [
            'score'      => (int)$row['score'],
            'issues'     => json_decode($row['issues_json'], true),
            'audited_at' => $row['audited_at'],
        ];
    }
}

$load_charts = true;
$page_title = 'SEO-Audit';
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>On-Page SEO-Audit</h1>
    <p>20 Regeln für bessere Suchmaschinenoptimierung</p>
  </div>
</div>

<div class="url-input-bar">
  <input type="text" id="auditUrl" value="<?= htmlspecialchars($testUrl) ?>" placeholder="deine-website.at" style="flex:1">
  <button class="btn btn-primary" onclick="runAudit()" id="auditBtn">Seite prüfen</button>
</div>

<?php if ($cached): ?>
<div class="refresh-bar">
  <span class="last-update">Zuletzt geprüft: <?= date('d.m.Y H:i', strtotime($cached['audited_at'])) ?></span>
</div>

<!-- Score -->
<div style="display:flex;justify-content:center;margin-bottom:32px">
  <div style="text-align:center">
    <canvas id="seoGauge" width="150" height="150"></canvas>
    <div class="score-label">SEO Score</div>
  </div>
</div>

<!-- Issues -->
<?php
$issues = $cached['issues'] ?? [];
$critical = array_filter($issues, fn($i) => $i['severity'] === 'critical');
$warnings = array_filter($issues, fn($i) => $i['severity'] === 'warning');
$infos    = array_filter($issues, fn($i) => $i['severity'] === 'info');
$passed   = 20 - count($issues);
?>

<div class="stats-row" style="margin-bottom:24px">
  <div class="stat-card-analytics">
    <div class="stat-value" style="color:var(--green)"><?= $passed ?></div>
    <div class="stat-label">Bestanden</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value" style="color:var(--red)"><?= count($critical) ?></div>
    <div class="stat-label">Kritisch</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value" style="color:var(--gold)"><?= count($warnings) ?></div>
    <div class="stat-label">Warnungen</div>
  </div>
  <div class="stat-card-analytics">
    <div class="stat-value" style="color:var(--accent)"><?= count($infos) ?></div>
    <div class="stat-label">Hinweise</div>
  </div>
</div>

<?php if (!empty($critical)): ?>
<div class="card" style="margin-bottom:16px">
  <div class="card-title" style="color:var(--red)">Kritische Probleme</div>
  <ul class="seo-issues">
    <?php foreach ($critical as $issue): ?>
    <li class="seo-issue">
      <?= severity_badge('critical') ?>
      <span class="seo-issue-text"><?= htmlspecialchars($issue['message']) ?></span>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?php if (!empty($warnings)): ?>
<div class="card" style="margin-bottom:16px">
  <div class="card-title" style="color:var(--gold)">Warnungen</div>
  <ul class="seo-issues">
    <?php foreach ($warnings as $issue): ?>
    <li class="seo-issue">
      <?= severity_badge('warning') ?>
      <span class="seo-issue-text"><?= htmlspecialchars($issue['message']) ?></span>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?php if (!empty($infos)): ?>
<div class="card" style="margin-bottom:16px">
  <div class="card-title" style="color:var(--accent)">Hinweise</div>
  <ul class="seo-issues">
    <?php foreach ($infos as $issue): ?>
    <li class="seo-issue">
      <?= severity_badge('info') ?>
      <span class="seo-issue-text"><?= htmlspecialchars($issue['message']) ?></span>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?php if ($passed > 0): ?>
<div class="card">
  <div class="card-title" style="color:var(--green)">Bestanden (<?= $passed ?>/20)</div>
  <p style="color:var(--text-muted);padding:12px 0"><?= $passed ?> von 20 SEO-Regeln wurden erfolgreich bestanden.</p>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  createGaugeChart('seoGauge', <?= $cached['score'] ?>, 'SEO Score');
});
</script>

<?php else: ?>
<div class="card">
  <div class="analytics-empty">
    <div class="empty-icon">✅</div>
    <h3>SEO-Audit starten</h3>
    <p>Gib die URL deiner Website ein und klicke "Seite prüfen" um eine SEO-Analyse durchzuführen.</p>
  </div>
</div>
<?php endif; ?>

<script>
function runAudit() {
  const btn = document.getElementById('auditBtn');
  btn.disabled = true;
  btn.textContent = 'Prüft...';
  let url = document.getElementById('auditUrl').value.trim();
  if (url && !url.match(/^https?:\/\//)) url = 'https://' + url;
  if (!url) { alert('Bitte URL eingeben'); btn.disabled = false; btn.textContent = 'Seite prüfen'; return; }
  const csrf = document.querySelector('meta[name="csrf"]').content;
  fetch('/api/run-seo-audit.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ url }),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      window.location.href = '?url=' + encodeURIComponent(url);
    } else {
      alert(d.error || 'Fehler');
      btn.disabled = false;
      btn.textContent = 'Seite prüfen';
    }
  })
  .catch(e => { alert('Verbindungsfehler: ' + e.message); btn.disabled = false; btn.textContent = 'Seite prüfen'; });
}
</script>

<?php require_once __DIR__ . '/../../partials/admin-footer.php'; ?>
