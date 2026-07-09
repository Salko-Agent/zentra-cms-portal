<?php
// admin/seo.php — edit SEO meta for all pages or a single page
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$focus_slug = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['slug'] ?? ''));
$pages = get_all_pages($project_id);

$page_title = 'SEO';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>SEO</h1>
    <p>Meta-Titel, Beschreibungen und Open-Graph für alle Seiten</p>
  </div>
</div>

<div class="tabs">
  <a href="/admin/seo.php" class="tab <?= !$focus_slug ? 'active' : '' ?>">Alle Seiten</a>
  <?php foreach ($pages as $p): ?>
  <a href="/admin/seo.php?slug=<?= urlencode($p['slug']) ?>"
     class="tab <?= ($focus_slug === $p['slug']) ? 'active' : '' ?>">
    <?= htmlspecialchars($p['label'] ?? ucfirst($p['slug'])) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php
$display_pages = $focus_slug
    ? array_filter($pages, fn($p) => $p['slug'] === $focus_slug)
    : $pages;

foreach ($display_pages as $p): ?>
<div class="card">
  <div class="card-title"><?= htmlspecialchars($p['label'] ?? ucfirst($p['slug'])) ?>
    <code style="font-weight:400;font-size:.75rem;color:var(--text-muted);margin-left:8px">/<?= htmlspecialchars($p['slug']) ?></code>
  </div>
  <form method="POST" class="seo-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
    <input type="hidden" name="page_id"    value="<?= (int)$p['id'] ?>">
    <div class="form-row">
      <div class="form-group">
        <label>SEO-Titel</label>
        <input type="text" name="seo_title" value="<?= htmlspecialchars($p['seo_title'] ?? '') ?>" maxlength="80">
        <div class="form-hint">Empfohlen: 50–60 Zeichen</div>
      </div>
      <div class="form-group">
        <label>OG-Titel</label>
        <input type="text" name="og_title" value="<?= htmlspecialchars($p['og_title'] ?? '') ?>" maxlength="95">
      </div>
    </div>
    <div class="form-group">
      <label>Meta-Beschreibung</label>
      <textarea name="seo_description" maxlength="200"><?= htmlspecialchars($p['seo_description'] ?? '') ?></textarea>
      <div class="form-hint">Empfohlen: 150–160 Zeichen</div>
    </div>
    <div class="form-group">
      <label>OG-Beschreibung</label>
      <textarea name="og_description" maxlength="300"><?= htmlspecialchars($p['og_description'] ?? '') ?></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>OG-Bild URL</label>
        <input type="url" name="og_image" value="<?= htmlspecialchars($p['og_image'] ?? '') ?>">
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:18px">
        <label class="toggle" style="margin-bottom:0">
          <input type="checkbox" name="noindex" value="1" <?= !empty($p['noindex']) ? 'checked' : '' ?>>
          <span class="toggle-slider"></span>
        </label>
        <span style="margin-left:10px;font-size:.82rem;color:var(--text-muted)">Seite von Google ausschließen (noindex)</span>
      </div>
    </div>
    <div style="text-align:right">
      <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
    </div>
  </form>
</div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>
