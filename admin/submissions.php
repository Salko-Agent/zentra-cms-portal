<?php
// admin/submissions.php — view, delete, export form submissions
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$db = db();

$type_filter = isset($_GET['type']) ? preg_replace('/[^a-z_]/', '', $_GET['type']) : '';

if ($type_filter) {
    $stmt = $db->prepare("SELECT * FROM form_submissions WHERE project_id = ? AND form_type = ? ORDER BY created_at DESC");
    $stmt->execute([$project_id, $type_filter]);
} else {
    $stmt = $db->prepare("SELECT * FROM form_submissions WHERE project_id = ? ORDER BY created_at DESC");
    $stmt->execute([$project_id]);
}
$subs = $stmt->fetchAll();

// Distinct form types for filter tabs
$types_stmt = $db->prepare("SELECT DISTINCT form_type FROM form_submissions WHERE project_id = ? ORDER BY form_type");
$types_stmt->execute([$project_id]);
$types = $types_stmt->fetchAll(\PDO::FETCH_COLUMN);

// Count unread
$unread_count = 0;
try {
    $unread_stmt = $db->prepare("SELECT COUNT(*) FROM form_submissions WHERE project_id = ? AND is_read = 0");
    $unread_stmt->execute([$project_id]);
    $unread_count = (int)$unread_stmt->fetchColumn();
} catch (Throwable $e) {
    // is_read column may not exist yet before migration
}

$page_title = 'Einsendungen';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Einsendungen</h1>
    <p><?= count($subs) ?> Einträge<?= $unread_count > 0 ? " &middot; <strong style=\"color:var(--accent)\">{$unread_count} ungelesen</strong>" : '' ?></p>
  </div>
  <div style="display:flex;gap:8px">
    <?php if ($unread_count > 0): ?>
    <button class="btn btn-secondary" onclick="markAllRead()">Alle als gelesen</button>
    <?php endif; ?>
    <a href="/api/submissions-manage.php?action=export&token=<?= urlencode(csrf_token()) ?>" class="btn btn-secondary">CSV Export ↓</a>
  </div>
</div>

<div class="tabs">
  <a href="/admin/submissions.php" class="tab <?= !$type_filter ? 'active' : '' ?>">Alle</a>
  <?php foreach ($types as $t): ?>
  <a href="/admin/submissions.php?type=<?= urlencode($t) ?>"
     class="tab <?= ($type_filter === $t) ? 'active' : '' ?>"><?= htmlspecialchars($t) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$subs): ?>
<div class="card" style="text-align:center;padding:48px;color:var(--text-muted)">Keine Einsendungen gefunden.</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
  <table class="data-table">
    <thead>
      <tr>
        <th style="width:12px"></th>
        <th>Datum</th>
        <th>Formular</th>
        <th>Name</th>
        <th>E-Mail</th>
        <th>Telefon</th>
        <th>Ziel / Interesse</th>
        <th>Nachricht</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($subs as $s):
        $d    = json_decode($s['data_json'], true) ?: [];
        $name = trim(($d['firstname'] ?? '') . ' ' . ($d['lastname'] ?? '')) ?: '—';
        $msg  = $d['message'] ?? '';
        if (mb_strlen($msg) > 60) $msg = mb_substr($msg, 0, 60) . '…';
        $is_read = !empty($s['is_read']);
    ?>
      <tr data-id="<?= $s['id'] ?>" style="<?= $is_read ? '' : 'background:var(--accent-soft)' ?>">
        <td style="padding:0 6px">
          <?php if (!$is_read): ?>
          <div style="width:6px;height:6px;background:var(--accent);border-radius:50%;margin:0 auto" title="Ungelesen"></div>
          <?php endif; ?>
        </td>
        <td data-label="Datum" style="color:var(--text-muted);font-size:.78rem;white-space:nowrap">
          <?= date('d.m.Y', strtotime($s['created_at'])) ?><br>
          <span style="color:var(--text-faint)"><?= date('H:i', strtotime($s['created_at'])) ?></span>
        </td>
        <td data-label="Formular"><span class="badge badge-warning"><?= htmlspecialchars($s['form_type'] ?? $s['page_slug']) ?></span></td>
        <td data-label="Name" style="font-weight:600;color:#fff"><?= htmlspecialchars($name) ?></td>
        <td data-label="E-Mail"><a href="mailto:<?= htmlspecialchars($d['email'] ?? '') ?>" style="color:var(--accent)"><?= htmlspecialchars($d['email'] ?? '—') ?></a></td>
        <td data-label="Telefon" style="color:var(--text-muted)"><?= htmlspecialchars($d['phone'] ?? '—') ?></td>
        <td data-label="Ziel" style="color:var(--text-muted);font-size:.82rem"><?= htmlspecialchars($d['goal'] ?? $d['interest'] ?? '—') ?></td>
        <td data-label="Nachricht" style="color:var(--text-muted);font-size:.8rem;max-width:200px"><?= htmlspecialchars($msg) ?></td>
        <td data-label="" style="text-align:right;white-space:nowrap">
          <?php if (!$is_read): ?>
          <button class="btn btn-secondary btn-sm" style="font-size:.7rem" onclick="markRead(<?= $s['id'] ?>, this)">&#10003;</button>
          <?php endif; ?>
          <button class="btn btn-danger btn-sm" style="font-size:.7rem" onclick="deleteSub(<?= $s['id'] ?>, this)">&times;</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php
$extra_js = <<<'JS'
const csrf = document.querySelector('meta[name="csrf"]')?.content || '';

async function deleteSub(id, btn) {
  if (!confirm('Einsendung wirklich löschen?')) return;
  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('id', id);
  fd.append('csrf_token', csrf);
  try {
    const res = await fetch('/api/submissions-manage.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      btn.closest('tr').remove();
      flash('Gelöscht', 'ok');
    } else flash(json.error || 'Fehler', 'err');
  } catch { flash('Netzwerkfehler', 'err'); }
}

async function markRead(id, btn) {
  const fd = new FormData();
  fd.append('action', 'mark_read');
  fd.append('id', id);
  fd.append('csrf_token', csrf);
  try {
    const res = await fetch('/api/submissions-manage.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      const row = btn.closest('tr');
      row.style.background = '';
      const dot = row.querySelector('div[title="Ungelesen"]');
      if (dot) dot.remove();
      btn.remove();
    }
  } catch {}
}

async function markAllRead() {
  const fd = new FormData();
  fd.append('action', 'mark_all_read');
  fd.append('csrf_token', csrf);
  try {
    const res = await fetch('/api/submissions-manage.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      flash('Alle als gelesen markiert ✓', 'ok');
      setTimeout(() => location.reload(), 600);
    }
  } catch { flash('Netzwerkfehler', 'err'); }
}
JS;
require_once __DIR__ . '/../partials/admin-footer.php';
?>
