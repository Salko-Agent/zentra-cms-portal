<?php
// admin/dashboard.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$db = db();

// Stats
$page_count    = $db->prepare('SELECT COUNT(*) FROM pages WHERE project_id = ?');
$page_count->execute([$project_id]);
$pages_n = (int)$page_count->fetchColumn();

$media_count = $db->prepare('SELECT COUNT(*) FROM media WHERE project_id = ?');
$media_count->execute([$project_id]);
$media_n = (int)$media_count->fetchColumn();

$sub_count = $db->prepare('SELECT COUNT(*) FROM form_submissions WHERE project_id = ?');
$sub_count->execute([$project_id]);
$subs_n = (int)$sub_count->fetchColumn();

$new_subs = $db->prepare("SELECT COUNT(*) FROM form_submissions WHERE project_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$new_subs->execute([$project_id]);
$new_subs_n = (int)$new_subs->fetchColumn();

// Pages list
$pages = get_all_pages($project_id);

// Recent submissions
$recent_stmt = $db->prepare("SELECT * FROM form_submissions WHERE project_id = ? ORDER BY created_at DESC LIMIT 5");
$recent_stmt->execute([$project_id]);
$recent_subs = $recent_stmt->fetchAll();

$page_title = 'Dashboard';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Übersicht für dein Projekt</p>
  </div>
  <a href="/admin/pages.php" class="btn btn-primary">Seiten verwalten →</a>
</div>

<div class="grid-4" style="margin-bottom:28px">
  <div class="stat-card" style="--stat-color:var(--accent);--stat-bg:var(--accent-soft)">
    <div class="stat-icon"><i data-lucide="file-text"></i></div>
    <div><div class="stat-n" data-count="<?= $pages_n ?>">0</div><div class="stat-l">Seiten</div></div>
  </div>
  <div class="stat-card" style="--stat-color:var(--cyan);--stat-bg:var(--cyan-bg)">
    <div class="stat-icon"><i data-lucide="image"></i></div>
    <div><div class="stat-n" data-count="<?= $media_n ?>">0</div><div class="stat-l">Medien</div></div>
  </div>
  <div class="stat-card" style="--stat-color:var(--purple);--stat-bg:var(--purple-bg)">
    <div class="stat-icon"><i data-lucide="mail"></i></div>
    <div><div class="stat-n" data-count="<?= $subs_n ?>">0</div><div class="stat-l">Einsendungen gesamt</div></div>
  </div>
  <div class="stat-card" style="--stat-color:var(--green);--stat-bg:var(--green-bg)">
    <div class="stat-icon"><i data-lucide="trending-up"></i></div>
    <div><div class="stat-n" data-count="<?= $new_subs_n ?>">0</div><div class="stat-l">Neu (7 Tage)</div></div>
  </div>
</div>

<div class="card">
  <div class="card-title">Seiten</div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Seite</th>
        <th>Slug</th>
        <th>Sektionen</th>
        <th>Zuletzt geändert</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
      <?php
        $sec_count = (int)($p['section_count'] ?? 0);
        $updated = $p['updated_at'] ? date('d.m.Y H:i', strtotime($p['updated_at'])) : '—';
      ?>
      <tr>
        <td data-label="Seite" style="font-weight:600;color:#fff"><?= htmlspecialchars($p['label'] ?? $p['slug']) ?></td>
        <td data-label="Slug"><code style="font-size:.78rem;color:var(--text-muted)">/<?= htmlspecialchars($p['slug']) ?></code></td>
        <td data-label="Sektionen"><?= $sec_count ?></td>
        <td data-label="Geändert" style="color:var(--text-muted);font-size:.82rem"><?= $updated ?></td>
        <td data-label="" style="text-align:right">
          <a href="/admin/page-edit.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-secondary btn-sm">Bearbeiten →</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($recent_subs): ?>
<div class="card">
  <div class="card-title">Neueste Einsendungen
    <a href="/admin/submissions.php" style="float:right;font-size:.75rem;color:var(--gold);font-weight:400">Alle ansehen →</a>
  </div>
  <table class="data-table">
    <thead>
      <tr><th>Datum</th><th>Formular</th><th>Name</th><th>E-Mail</th></tr>
    </thead>
    <tbody>
    <?php foreach ($recent_subs as $s):
        $data = json_decode($s['data_json'], true) ?: [];
        $name = trim(($data['firstname'] ?? '') . ' ' . ($data['lastname'] ?? '')) ?: '—';
    ?>
      <tr>
        <td data-label="Datum" style="color:var(--text-muted);font-size:.8rem"><?= date('d.m.Y H:i', strtotime($s['created_at'])) ?></td>
        <td data-label="Formular"><span class="badge badge-warning"><?= htmlspecialchars($s['page_slug'] ?? '—') ?></span></td>
        <td data-label="Name"><?= htmlspecialchars($name) ?></td>
        <td data-label="E-Mail"><?= htmlspecialchars($data['email'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>
