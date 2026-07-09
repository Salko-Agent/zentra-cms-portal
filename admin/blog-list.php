<?php
// admin/blog-list.php — Blog post list
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$db = db();

// Fetch all posts for this project
$stmt = $db->prepare(
    'SELECT id, title, slug, category, status, published_at, created_at, updated_at
     FROM blog_posts
     WHERE project_id = ?
     ORDER BY created_at DESC'
);
$stmt->execute([$project_id]);
$posts = $stmt->fetchAll();

// Stats
$total     = count($posts);
$published = 0;
$drafts    = 0;
foreach ($posts as $p) {
    if ($p['status'] === 'published') $published++;
    else $drafts++;
}

$page_title = 'Blog';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Blog</h1>
    <p>Blogbeiträge verwalten</p>
  </div>
  <a href="/admin/blog-edit.php" class="btn btn-primary">
    <i data-lucide="plus" style="width:15px;height:15px;vertical-align:-2px;margin-right:5px"></i>Neuer Beitrag
  </a>
</div>

<!-- Stats row -->
<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);font-size:.82rem">
    <i data-lucide="file-text" style="width:15px;height:15px;color:var(--text-muted)"></i>
    <span style="color:var(--text-muted)">Gesamt</span>
    <span style="font-weight:700;color:#fff;font-size:.95rem"><?= $total ?></span>
  </div>
  <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);font-size:.82rem">
    <i data-lucide="circle-check" style="width:15px;height:15px;color:var(--green)"></i>
    <span style="color:var(--text-muted)">Veröffentlicht</span>
    <span style="font-weight:700;color:var(--green);font-size:.95rem"><?= $published ?></span>
  </div>
  <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);font-size:.82rem">
    <i data-lucide="clock" style="width:15px;height:15px;color:var(--text-muted)"></i>
    <span style="color:var(--text-muted)">Entwürfe</span>
    <span style="font-weight:700;color:var(--text-muted);font-size:.95rem"><?= $drafts ?></span>
  </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <?php if ($posts): ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Titel</th>
        <th>Kategorie</th>
        <th>Status</th>
        <th>Veröffentlicht am</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="blogTableBody">
    <?php foreach ($posts as $p):
        $pub_date = $p['published_at'] ? date('d.m.Y', strtotime($p['published_at'])) : '—';
    ?>
      <tr data-id="<?= (int)$p['id'] ?>">
        <td data-label="Titel">
          <div style="font-weight:600;color:#fff;margin-bottom:2px"><?= h($p['title']) ?></div>
          <div style="font-size:.75rem;color:var(--text-faint)">/blog/<?= h($p['slug']) ?></div>
        </td>
        <td data-label="Kategorie">
          <?php if ($p['category']): ?>
            <span class="badge badge-gray"><?= h($p['category']) ?></span>
          <?php else: ?>
            <span style="color:var(--text-faint);font-size:.8rem">—</span>
          <?php endif; ?>
        </td>
        <td data-label="Status">
          <?php if ($p['status'] === 'published'): ?>
            <span class="badge badge-success">Veröffentlicht</span>
          <?php else: ?>
            <span class="badge badge-gray">Entwurf</span>
          <?php endif; ?>
        </td>
        <td data-label="Veröffentlicht am" style="color:var(--text-muted);font-size:.82rem"><?= $pub_date ?></td>
        <td data-label="" style="text-align:right;white-space:nowrap">
          <a href="/admin/blog-edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-secondary btn-sm">Bearbeiten →</a>
          <button class="btn btn-danger btn-sm" style="margin-left:4px"
                  onclick="deletePost(<?= (int)$p['id'] ?>, '<?= h(addslashes($p['title'])) ?>')">×</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div style="text-align:center;padding:60px 20px;color:var(--text-muted)">
    <i data-lucide="file-text" style="width:40px;height:40px;margin-bottom:16px;opacity:.3"></i>
    <p style="font-size:.95rem;margin-bottom:8px">Noch keine Beiträge vorhanden</p>
    <p style="font-size:.8rem;color:var(--text-faint);margin-bottom:20px">Erstelle deinen ersten Blogbeitrag.</p>
    <a href="/admin/blog-edit.php" class="btn btn-primary">+ Neuer Beitrag</a>
  </div>
  <?php endif; ?>
</div>

<?php
$extra_js = <<<'JS'
async function deletePost(id, title) {
  if (!confirm('Beitrag "' + title + '" wirklich löschen?')) return;

  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('blog_post_id', id);
  fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

  try {
    const res = await fetch('/api/blog-manage.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      flash('Beitrag gelöscht', 'ok');
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) row.remove();
    } else {
      flash(json.error || 'Fehler beim Löschen', 'err');
    }
  } catch (e) {
    flash('Netzwerkfehler', 'err');
  }
}
JS;
require_once __DIR__ . '/../partials/admin-footer.php';
?>
