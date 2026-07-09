<?php
// admin/pages.php — list all pages with create + delete
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$pages = get_all_pages($project_id);
$db = db();

$page_title = 'Seiten';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Seiten</h1>
    <p><?= count($pages) ?> Seiten in diesem Projekt</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">+ Neue Seite</button>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <table class="data-table">
    <thead>
      <tr>
        <th>Seite</th>
        <th>URL</th>
        <th>Sektionen</th>
        <th>SEO-Titel</th>
        <th>Zuletzt geändert</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="pagesBody">
    <?php foreach ($pages as $p):
        $sec_cnt = (int)($p['section_count'] ?? 0);
        $updated = !empty($p['updated_at']) ? date('d.m.Y H:i', strtotime($p['updated_at'])) : '—';
    ?>
      <tr data-id="<?= $p['id'] ?>">
        <td data-label="Seite" style="font-weight:600;color:#fff"><?= htmlspecialchars($p['label'] ?? ucfirst($p['slug'])) ?></td>
        <td data-label="URL"><code style="font-size:.78rem;color:var(--text-muted)">/<?= htmlspecialchars($p['slug']) ?></code></td>
        <td data-label="Sektionen"><span class="badge badge-gray"><?= $sec_cnt ?></span></td>
        <td data-label="SEO-Titel" style="color:var(--text-muted);font-size:.82rem;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <?= htmlspecialchars($p['seo_title'] ?: '—') ?>
        </td>
        <td data-label="Geändert" style="color:var(--text-muted);font-size:.8rem"><?= $updated ?></td>
        <td data-label="" style="text-align:right;white-space:nowrap">
          <a href="/admin/page-edit.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-secondary btn-sm">Inhalt →</a>
          <a href="/admin/seo.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-secondary btn-sm" style="margin-left:4px">SEO</a>
          <?php if ($p['slug'] !== 'home'): ?>
          <button class="btn btn-danger btn-sm" style="margin-left:4px"
                  onclick="deletePage(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['label']), ENT_QUOTES) ?>')">×</button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Create Page Modal -->
<div id="createModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.6);align-items:center;justify-content:center;padding:20px">
  <div class="card" style="max-width:440px;width:100%;margin:0">
    <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
      Neue Seite erstellen
      <button onclick="this.closest('#createModal').style.display='none'" style="background:none;border:none;color:var(--text-muted);font-size:1.2rem;cursor:pointer">&#10005;</button>
    </div>
    <form id="createPageForm" onsubmit="createPage(event)">
      <div class="form-group">
        <label>Seitenname *</label>
        <input type="text" name="label" placeholder="z.B. Über uns" required>
      </div>
      <div class="form-group">
        <label>URL-Slug <span style="font-weight:400;color:var(--text-faint)">(optional, wird automatisch erstellt)</span></label>
        <input type="text" name="slug" placeholder="z.B. ueber-uns" pattern="[a-z0-9\-]*">
        <div class="form-hint">Nur Kleinbuchstaben, Zahlen und Bindestriche</div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Abbrechen</button>
        <button type="submit" class="btn btn-primary">Seite erstellen</button>
      </div>
    </form>
  </div>
</div>

<?php
$extra_js = <<<'JS'
async function createPage(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  fd.append('action', 'create');
  fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

  try {
    const res = await fetch('/api/pages.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      flash('Seite erstellt ✓', 'ok');
      setTimeout(() => location.reload(), 600);
    } else {
      flash(json.error || 'Fehler', 'err');
    }
  } catch { flash('Netzwerkfehler', 'err'); }
}

async function deletePage(id, label) {
  if (!confirm('Seite "' + label + '" wirklich löschen?\n\nAlle Sektionen und Inhalte werden unwiderruflich gelöscht.')) return;

  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('page_id', id);
  fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

  try {
    const res = await fetch('/api/pages.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      flash('Seite gelöscht', 'ok');
      document.querySelector(`tr[data-id="${id}"]`)?.remove();
    } else {
      flash(json.error || 'Fehler', 'err');
    }
  } catch { flash('Netzwerkfehler', 'err'); }
}
JS;
require_once __DIR__ . '/../partials/admin-footer.php';
?>
