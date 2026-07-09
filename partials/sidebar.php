<?php
// partials/sidebar.php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_path = $_SERVER['REQUEST_URI'];
function is_active(string $match): string {
    global $current_path;
    return (strpos($current_path, $match) !== false) ? ' active' : '';
}
$project = get_project($_SESSION['project_id'] ?? 0);
$user    = current_user();
?>
<aside class="sidebar">
  <div class="sidebar-top">
    <a href="/admin/dashboard.php" class="brand">
      <div class="brand-icon"><i data-lucide="zap"></i></div>
      <div>
        <div class="brand-name">Zentra CMS</div>
        <div class="brand-sub">Content Management</div>
      </div>
    </a>
  </div>
  <?php if (!empty($_SESSION['is_super_admin'])): ?>
  <div class="sidebar-project">
    <span style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em">Aktives Projekt</span><br>
    <strong><?= htmlspecialchars($project['name'] ?? '—') ?></strong>
  </div>
  <?php elseif ($project): ?>
  <div class="sidebar-project">
    Projekt
    <strong><?= htmlspecialchars($project['name'] ?? '') ?></strong>
  </div>
  <?php endif; ?>

  <nav>
    <div data-section="content">
    <div class="nav-label">Inhalt</div>
    <a href="/admin/dashboard.php" class="<?= is_active('/dashboard') ?>">
      <i data-lucide="layout-dashboard"></i> Dashboard
    </a>
    <a href="/admin/pages.php" class="<?= is_active('/pages') ?>">
      <i data-lucide="file-text"></i> Seiten
    </a>
    <a href="/admin/seo.php" class="<?= is_active('/seo') ?>">
      <i data-lucide="search"></i> SEO
    </a>
    <a href="/admin/blog-list.php" class="<?= is_active('/blog') ?>">
      <i data-lucide="book-open"></i> Blog
    </a>
    </div>

    <div data-section="analytics">
    <div class="nav-label">Analytics</div>
    <a href="/admin/analytics/overview.php" class="<?= is_active('/analytics/overview') ?>">
      <i data-lucide="bar-chart-3"></i> Übersicht
    </a>
    <a href="/admin/analytics/search-console.php" class="<?= is_active('/analytics/search-console') ?>">
      <i data-lucide="globe"></i> Search Console
    </a>
    <a href="/admin/analytics/pagespeed.php" class="<?= is_active('/analytics/pagespeed') ?>">
      <i data-lucide="zap"></i> PageSpeed
    </a>
    <a href="/admin/analytics/seo-audit.php" class="<?= is_active('/analytics/seo-audit') ?>">
      <i data-lucide="shield-check"></i> SEO-Audit
    </a>
    <?php if (!empty($_SESSION['is_super_admin'])): ?>
    <a href="/admin/analytics/activity-log.php" class="<?= is_active('/analytics/activity-log') ?>">
      <i data-lucide="activity"></i> Aktivitätslog
    </a>
    <?php endif; ?>
    </div>

    <div data-section="media">
    <div class="nav-label">Medien & Einstellungen</div>
    <a href="/admin/media.php" class="<?= is_active('/media') ?>">
      <i data-lucide="image"></i> Medien
    </a>
    </div>
    <div data-section="settings">
    <a href="/admin/settings.php" class="<?= is_active('/settings') ?>">
      <i data-lucide="settings"></i> Einstellungen
    </a>
    <a href="/admin/submissions.php" class="<?= is_active('/submissions') ?>">
      <i data-lucide="mail"></i> Einsendungen
    </a>
    </div>

    <?php if (!empty($_SESSION['is_super_admin'])): ?>
    <div data-section="projects">
    <div class="nav-label">Projekte wechseln</div>
    <?php foreach (get_all_projects() as $p): ?>
    <form method="POST" action="/admin/switch-project.php" style="margin:0;padding:0">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <button type="submit" class="sidebar-project-btn"
         style="<?= ((int)$_SESSION['project_id'] === (int)$p['id']) ? 'color:var(--gold);font-weight:600' : '' ?>">
         <i data-lucide="folder"></i> <?= h($p['name']) ?>
      </button>
    </form>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="nav-label">Konto</div>
    <a href="/admin/password.php" class="<?= is_active('/password') ?>">
      <i data-lucide="lock"></i> Passwort ändern
    </a>
  </nav>

  <div class="sidebar-footer">
    <div style="margin-bottom:6px;display:flex;align-items:center;gap:6px"><i data-lucide="user" style="width:14px;height:14px"></i> <?= htmlspecialchars($user['name'] ?? '') ?></div>
    <a href="/logout.php">Abmelden →</a>
  </div>
</aside>
