<?php
// admin/analytics/activity-log.php — Activity Log Viewer
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/analytics-helpers.php';

auth_start();
auth_check();

// Activity log is super-admin only
if (empty($_SESSION['is_super_admin'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$project_id = (int)$_SESSION['project_id'];
$is_admin   = !empty($_SESSION['is_super_admin']);

// Filters
$filter_action = $_GET['action'] ?? '';
$filter_user   = $_GET['user'] ?? '';
$filter_project = ($is_admin && isset($_GET['project'])) ? (int)$_GET['project'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];

if ($is_admin && $filter_project > 0) {
    $where[] = 'al.project_id = ?';
    $params[] = $filter_project;
} elseif (!$is_admin) {
    $where[] = 'al.project_id = ?';
    $params[] = $project_id;
}

if ($filter_action) {
    $where[] = 'al.action = ?';
    $params[] = $filter_action;
}
if ($filter_user) {
    $where[] = 'al.user_id = ?';
    $params[] = (int)$filter_user;
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countStmt = db()->prepare("SELECT COUNT(*) FROM activity_log al $whereSQL");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $per_page));

// Fetch rows
$sql = "SELECT al.*, u.name as user_name, p.name as project_name
        FROM activity_log al
        LEFT JOIN users u ON u.id = al.user_id
        LEFT JOIN projects p ON p.id = al.project_id
        $whereSQL
        ORDER BY al.created_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Available actions for filter
$actions = ['content_saved','settings_saved','seo_saved','section_created','section_deleted',
            'page_created','page_deleted','media_uploaded','media_deleted',
            'user_login','user_logout','password_changed','project_switched'];

// Available users
$usersStmt = db()->prepare($is_admin
    ? 'SELECT DISTINCT u.id, u.name FROM users u INNER JOIN activity_log al ON al.user_id = u.id ORDER BY u.name'
    : 'SELECT DISTINCT u.id, u.name FROM users u INNER JOIN activity_log al ON al.user_id = u.id WHERE al.project_id = ? ORDER BY u.name');
$usersStmt->execute($is_admin ? [] : [$project_id]);
$users = $usersStmt->fetchAll();

$load_charts = true; // for analytics.css
$page_title = 'Aktivitätslog';
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Aktivitätslog</h1>
    <p><?= number_format($total) ?> Einträge</p>
  </div>
</div>

<div class="filter-bar">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <?php if ($is_admin):
      $projects = get_all_projects();
    ?>
    <select name="project" onchange="this.form.submit()">
      <option value="0">Alle Projekte</option>
      <?php foreach ($projects as $p): ?>
      <option value="<?= $p['id'] ?>" <?= $filter_project === (int)$p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <select name="action" onchange="this.form.submit()">
      <option value="">Alle Aktionen</option>
      <?php foreach ($actions as $a): ?>
      <option value="<?= $a ?>" <?= $filter_action === $a ? 'selected' : '' ?>><?= $a ?></option>
      <?php endforeach; ?>
    </select>

    <select name="user" onchange="this.form.submit()">
      <option value="">Alle Benutzer</option>
      <?php foreach ($users as $u): ?>
      <option value="<?= $u['id'] ?>" <?= $filter_user == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <?php if ($filter_action || $filter_user || $filter_project): ?>
    <a href="?page=1" class="btn btn-secondary btn-sm">Filter zurücksetzen</a>
    <?php endif; ?>
  </form>
</div>

<div class="card">
  <?php if (empty($logs)): ?>
  <div class="analytics-empty">
    <div class="empty-icon">📋</div>
    <h3>Keine Einträge</h3>
    <p>Es gibt noch keine Aktivitäten mit diesen Filtern.</p>
  </div>
  <?php else: ?>
  <table class="activity-table">
    <thead>
      <tr>
        <th>Zeitpunkt</th>
        <?php if ($is_admin): ?><th>Projekt</th><?php endif; ?>
        <th>Benutzer</th>
        <th>Aktion</th>
        <th>Details</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log):
        $meta = $log['meta_json'] ? json_decode($log['meta_json'], true) : [];
      ?>
      <tr>
        <td data-label="Zeitpunkt" class="activity-time" title="<?= htmlspecialchars($log['created_at']) ?>">
          <?= date('d.m.Y H:i', strtotime($log['created_at'])) ?>
        </td>
        <?php if ($is_admin): ?>
        <td data-label="Projekt"><?= htmlspecialchars($log['project_name'] ?? '—') ?></td>
        <?php endif; ?>
        <td data-label="Benutzer"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
        <td data-label="Aktion"><?= activity_action_badge($log['action']) ?></td>
        <td data-label="Details" style="font-size:.82rem;color:var(--text-muted)">
          <?php
          $details = [];
          if ($log['target_type']) $details[] = htmlspecialchars($log['target_type']);
          if ($log['target_label']) $details[] = htmlspecialchars($log['target_label']);
          if ($log['target_id']) $details[] = '#' . $log['target_id'];
          if (!empty($meta['ip'])) $details[] = 'IP: ' . htmlspecialchars($meta['ip']);
          echo implode(' · ', $details);
          ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&project=<?= $filter_project ?>">← Zurück</a>
    <?php endif; ?>
    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <?php if ($i === $page): ?>
    <span class="current"><?= $i ?></span>
    <?php else: ?>
    <a href="?page=<?= $i ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&project=<?= $filter_project ?>"><?= $i ?></a>
    <?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&project=<?= $filter_project ?>">Weiter →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../partials/admin-footer.php'; ?>
