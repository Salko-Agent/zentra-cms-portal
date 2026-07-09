<?php
// admin/media.php — media library with upload, delete, alt-text
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$db = db();

$stmt = $db->prepare('SELECT * FROM media WHERE project_id = ? ORDER BY created_at DESC');
$stmt->execute([$project_id]);
$media = $stmt->fetchAll();

$page_title = 'Medien';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div>
    <h1>Medien</h1>
    <p><?= count($media) ?> Dateien hochgeladen</p>
  </div>
</div>

<div class="upload-zone" id="uploadZone">
  <input type="file" id="uploadInput" accept="image/*" style="display:none">
  <i data-lucide="upload-cloud" style="width:36px;height:36px;color:var(--accent);margin-bottom:10px;opacity:.7"></i>
  <p style="font-weight:600;color:var(--text)">Bild hierher ziehen oder klicken</p>
  <p style="font-size:.72rem;margin-top:6px">JPG, PNG, WebP, GIF · max. 10 MB · wird als WebP gespeichert</p>
</div>

<!-- Search bar -->
<div class="media-search-bar">
  <input type="text" id="mediaSearch" placeholder="Dateinamen suchen…" autocomplete="off">
  <span class="media-count-label" id="mediaCountLabel"><?= count($media) ?> Bilder</span>
</div>

<div class="media-grid-enhanced" id="mediaGrid">
  <?php foreach ($media as $m):
    // Try to get file size from local disk
    $local = __DIR__ . '/../uploads/' . $project_id . '/' . basename($m['filename']);
    $fsize = file_exists($local) ? round(filesize($local) / 1024) . ' KB' : '';
  ?>
  <div class="media-card" data-id="<?= $m['id'] ?>" data-filename="<?= h(strtolower($m['filename'])) ?>">
    <div class="media-card-img">
      <img src="<?= h($m['url']) ?>" alt="<?= h($m['alt_text'] ?: $m['filename']) ?>" loading="lazy">
      <div class="media-hover-overlay">
        <button class="btn btn-sm btn-secondary" data-copy="<?= h($m['url']) ?>" style="font-size:.72rem" title="URL kopieren">
          <i data-lucide="copy" style="width:13px;height:13px"></i>
        </button>
        <button class="btn btn-sm btn-danger" onclick="deleteMedia(<?= $m['id'] ?>, this)" style="font-size:.72rem" title="Löschen">
          <i data-lucide="trash-2" style="width:13px;height:13px"></i>
        </button>
      </div>
    </div>
    <div class="media-card-body">
      <div class="media-card-name" title="<?= h($m['url']) ?>"><?= h($m['filename']) ?></div>
      <?php if ($fsize): ?>
      <div class="media-card-meta"><?= $fsize ?></div>
      <?php endif; ?>
      <input type="text" class="media-card-alt alt-input" data-id="<?= $m['id'] ?>"
             value="<?= h($m['alt_text']) ?>" placeholder="Alt-Text hinzufügen…">
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$media): ?>
  <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted)">
    <i data-lucide="image-off" style="width:48px;height:48px;opacity:.3;margin-bottom:14px"></i>
    <p>Noch keine Dateien hochgeladen.</p>
  </div>
  <?php endif; ?>
</div>

<?php
$extra_js = <<<'JS'
// Upload zone bindings
document.getElementById('uploadZone').addEventListener('click', function(e) {
  if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) document.getElementById('uploadInput').click();
});
document.getElementById('uploadInput').addEventListener('change', function() {
  if (this.files.length) {
    window.handleUpload && handleUpload(this.files[0], document.getElementById('uploadZone'));
  }
});
window.reloadMediaGrid = function() { location.reload(); };

// Media search filter
const mediaSearch = document.getElementById('mediaSearch');
const mediaCountLabel = document.getElementById('mediaCountLabel');
if (mediaSearch) {
  mediaSearch.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const cards = document.querySelectorAll('#mediaGrid .media-card');
    let visible = 0;
    cards.forEach(card => {
      const name = card.dataset.filename || '';
      const show = !q || name.includes(q);
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (mediaCountLabel) mediaCountLabel.textContent = visible + ' Bilder';
  });
}

// Delete media
async function deleteMedia(id, btn) {
  if (!confirm('Bild wirklich löschen?')) return;
  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('media_id', id);
  fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

  try {
    const res = await fetch('/api/media-manage.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      btn.closest('.media-card').remove();
      flash('Bild gelöscht', 'ok');
    } else {
      flash(json.error || 'Fehler', 'err');
    }
  } catch { flash('Netzwerkfehler', 'err'); }
}

// Save alt text on blur
let altDebounce = {};
document.querySelectorAll('.alt-input').forEach(inp => {
  inp.addEventListener('change', async function() {
    const id = this.dataset.id;
    const fd = new FormData();
    fd.append('action', 'update_alt');
    fd.append('media_id', id);
    fd.append('alt_text', this.value);
    fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');

    try {
      const res = await fetch('/api/media-manage.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.ok) flash('Alt-Text gespeichert ✓', 'ok');
    } catch {}
  });
});
JS;
require_once __DIR__ . '/../partials/admin-footer.php';
?>
