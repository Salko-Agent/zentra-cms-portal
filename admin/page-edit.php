<?php
// admin/page-edit.php — Section editor with accordion + preview
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$slug = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['slug'] ?? ''));
if (!$slug) { header('Location: /admin/pages.php'); exit; }

$page = get_page_by_slug($project_id, $slug);
if (!$page) { header('Location: /admin/pages.php'); exit; }

$db       = db();
$sections = get_sections_for_page((int)$page['id']);
$csrf     = csrf_token();

$page_title = htmlspecialchars($page['label'] ?? ucfirst($slug));

// ── Live preview setup ────────────────────────────────────────
$settings         = get_settings($project_id);
$preview_url_base = rtrim(trim($settings['preview_url'] ?? ''), '/');
$project_row      = get_project($project_id);
$api_key          = $project_row['api_key'] ?? '';

// Auto-derive preview URL from webhook_url when not explicitly configured
if ($preview_url_base === '' && !empty($project_row['webhook_url'])) {
    $preview_url_base = rtrim(preg_replace('/_webhook\.php.*$/', '', $project_row['webhook_url']), '/');
}

$use_live_preview = ($preview_url_base !== '');

if ($use_live_preview) {
    $page_path  = ($slug === 'home') ? '/' : '/' . $slug;
    $iframe_src = $preview_url_base . $page_path;
    $reload_url = $iframe_src;
} else {
    $iframe_src = '/api/preview.php?page=' . urlencode($slug);
    $reload_url = $iframe_src;
}

// ── Section type icons (Lucide) ───────────────────────────────
$type_icons = [
    'hero'            => 'layout-template',
    'stats_strip'     => 'bar-chart-2',
    'partners'        => 'building-2',
    'image_text'      => 'image',
    'list_items'      => 'list',
    'team_members'    => 'users',
    'trainer_profile' => 'user-circle-2',
    'testimonials'    => 'quote',
    'pricing_cards'   => 'credit-card',
    'cta'             => 'megaphone',
    'steps'           => 'footprints',
    'price_table'     => 'table-2',
    'contact_info'    => 'phone',
    'blog_articles'   => 'book-open',
    'legal'           => 'file-text',
    'room_listing'    => 'home',
    'faq'             => 'help-circle',
    'portfolio'       => 'grid',
    'timeline'        => 'clock',
];

// ── Collect all page images ───────────────────────────────────
$page_images = [];
foreach ($sections as $sec) {
    $fields = get_section_fields((int)$sec['id']);
    foreach ($fields as $fk => $fv) {
        $val = is_array($fv) ? ($fv['value'] ?? '') : $fv;
        if ($val && (strpos($val, '/uploads/') === 0 || preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $val))) {
            $page_images[] = ['url' => $val, 'field' => $fk, 'section' => $sec['label'] ?? $sec['section_type']];
        }
    }
    $items = get_section_items((int)$sec['id']);
    foreach ($items as $item) {
        $data = json_decode($item['item_json'] ?? '[]', true);
        if (is_array($data)) {
            array_walk_recursive($data, function($val, $key) use (&$page_images, $sec) {
                if (is_string($val) && $val && (strpos($val, '/uploads/') === 0 || preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $val))) {
                    $page_images[] = ['url' => $val, 'field' => $key, 'section' => $sec['label'] ?? $sec['section_type']];
                }
            });
        }
    }
}
// De-duplicate by URL
$seen_urls = [];
$page_images = array_filter($page_images, function($img) use (&$seen_urls) {
    if (isset($seen_urls[$img['url']])) return false;
    $seen_urls[$img['url']] = true;
    return true;
});

require_once __DIR__ . '/../partials/admin-header.php';
?>

<style>
.acc-body { display:none; padding:20px 18px; border-bottom:1px solid var(--border); }
.acc-body.show { display:block; }
.acc-hdr.active { color:var(--accent); border-left:2px solid var(--accent); }
</style>

<div class="page-header">
  <div>
    <h1><?= $page_title ?></h1>
    <p>Sektionen bearbeiten · <a href="/admin/seo.php?slug=<?= urlencode($slug) ?>" style="color:var(--accent)">SEO bearbeiten</a> · <span style="color:var(--text-faint);font-size:.72rem">Ctrl+S zum Speichern</span></p>
  </div>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <span id="lastSavedBar" style="margin-right:4px"></span>
    <a href="/admin/pages.php" class="btn btn-secondary btn-sm">← Zurück</a>
    <button id="indexBtn" class="btn btn-sm" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);color:#fff;display:flex;align-items:center;gap:6px" onclick="requestIndexing()" title="Diese Seite bei Google zur Indexierung einreichen">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Google indexieren
    </button>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('addSectionModal').style.display='flex'">+ Sektion</button>
  </div>
</div>

<script>
function requestIndexing() {
  const btn = document.getElementById('indexBtn');
  btn.disabled = true;
  btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Wird eingereicht…';

  const fd = new FormData();
  fd.append('csrf_token', '<?= $csrf ?>');
  fd.append('slug', '<?= htmlspecialchars($slug, ENT_QUOTES) ?>');

  fetch('/api/request-indexing.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        btn.style.background = 'rgba(34,197,94,0.15)';
        btn.style.borderColor = 'rgba(34,197,94,0.5)';
        btn.style.color = '#22c55e';
        btn.innerHTML = '✓ Eingereicht';
        setTimeout(() => {
          btn.disabled = false;
          btn.style.cssText = 'background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);color:#fff;display:flex;align-items:center;gap:6px';
          btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Google indexieren';
        }, 4000);
      } else {
        btn.disabled = false;
        btn.style.borderColor = 'rgba(239,68,68,0.5)';
        btn.style.color = '#ef4444';
        btn.innerHTML = '✗ Fehler';
        alert('Fehler: ' + (data.error || 'Unbekannter Fehler'));
        setTimeout(() => {
          btn.style.cssText = 'background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);color:#fff;display:flex;align-items:center;gap:6px';
          btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Google indexieren';
        }, 3000);
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '✗ Verbindungsfehler';
    });
}
</script>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<div class="editor-layout">
  <div class="editor-sections-panel">

    <!-- Sections toolbar: section count + collapse/expand -->
    <div class="sections-toolbar">
      <div class="sections-toolbar-left">
        <i data-lucide="layers" style="width:13px;height:13px"></i>
        <span><?= count($sections) ?> Sektionen</span>
      </div>
      <div class="sections-toolbar-right">
        <button class="collapse-btn" data-expand-all title="Alle öffnen">↓ Alle</button>
        <button class="collapse-btn" data-collapse-all title="Alle schließen">↑ Schließen</button>
      </div>
    </div>

    <div id="sectionsList" data-page-id="<?= (int)$page['id'] ?>">
    <?php $first = true; foreach ($sections as $sec):
        $sec_data_raw = get_section_fields((int)$sec['id']);
        $sec_data = [];
        foreach ($sec_data_raw as $k => $v) {
            $sec_data[$k] = is_array($v) ? ($v['value'] ?? '') : $v;
        }
        $sec_items  = get_section_items((int)$sec['id']);
        $type       = $sec['section_type'];
        $label      = $sec['label'] ?? ucwords(str_replace('_', ' ', $type));
        $enabled    = (bool)($sec['enabled'] ?? true);
        $form_file  = __DIR__ . '/../includes/section-forms/' . $type . '.php';
        $sid        = (int)$sec['id'];
        $icon       = $type_icons[$type] ?? 'square';
    ?>
    <div class="section-accordion" data-section-id="<?= $sid ?>">
      <div class="section-accordion-header acc-hdr <?= $first ? 'active' : '' ?>" onclick="toggleAcc(this)">
        <div style="display:flex;align-items:center;gap:10px;min-width:0">
          <span class="section-drag-handle" title="Ziehen zum Sortieren" onclick="event.stopPropagation()">⠿</span>
          <label class="toggle" style="margin-bottom:0;flex-shrink:0" onclick="event.stopPropagation()">
            <input type="checkbox" class="section-toggle" data-section-id="<?= $sid ?>" <?= $enabled ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
          <i data-lucide="<?= h($icon) ?>" style="width:14px;height:14px;flex-shrink:0;color:var(--text-faint)"></i>
          <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($label) ?></span>
          <span class="unsaved-dot" title="Ungespeicherte Änderungen"></span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
          <span class="section-type-badge" data-type="<?= h($type) ?>"><?= h($type) ?></span>
          <button type="button" class="section-delete-btn" onclick="event.stopPropagation();deleteSection(<?= $sid ?>,'<?= h($label) ?>')" title="Sektion löschen">✕</button>
        </div>
      </div>
      <div class="acc-body <?= $first ? 'show' : '' ?>" id="accBody<?= $sid ?>">
        <?php if (file_exists($form_file)):
            include $form_file;
        else: ?>
          <p style="color:var(--text-muted);font-size:.82rem">Kein Formular für Typ "<?= h($type) ?>".</p>
        <?php endif; ?>
      </div>
    </div>
    <?php $first = false; endforeach; ?>

    <?php if (empty($sections)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted)">
      Keine Sektionen vorhanden. Klicke oben auf "+ Sektion" um eine hinzuzufügen.
    </div>
    <?php endif; ?>
    </div>

    <!-- Page images panel -->
    <?php if (!empty($page_images)): ?>
    <div class="page-images-panel">
      <div class="page-images-panel-title">
        <i data-lucide="image" style="width:12px;height:12px"></i>
        Seiten-Bilder (<?= count($page_images) ?>)
        <span style="font-weight:400;color:var(--text-faint);font-size:.64rem;margin-left:4px">· Klicken zum Austauschen</span>
      </div>
      <div class="page-images-grid">
        <?php foreach ($page_images as $pi): ?>
        <div class="page-img-thumb media-picker-btn" data-target="" data-swap-url="<?= h($pi['url']) ?>"
             title="<?= h($pi['section']) ?> · <?= h($pi['field']) ?>">
          <img src="<?= h($pi['url']) ?>" alt="" loading="lazy">
          <div class="swap-overlay">Tauschen</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <div class="preview-panel">
    <!-- Preview toolbar with device toggle -->
    <div class="preview-toolbar">
      <div class="preview-toolbar-dots">
        <span style="background:#ff5f57"></span>
        <span style="background:#febc2e"></span>
        <span style="background:#28c840"></span>
      </div>

      <!-- Device toggle -->
      <div class="device-toggle">
        <button class="device-btn active" data-device="desktop" title="Desktop">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </button>
        <button class="device-btn" data-device="tablet" title="Tablet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
        </button>
        <button class="device-btn" data-device="mobile" title="Mobile">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1"/></svg>
        </button>
      </div>

      <?php if ($use_live_preview): ?>
        <span style="font-size:.72rem;display:flex;align-items:center;gap:5px;flex:1;min-width:0">
          <span class="live-dot"></span>
          <span class="preview-url-label"><?= h($preview_url_base . (($slug === 'home') ? '/' : '/' . $slug)) ?></span>
        </span>
        <a href="<?= h($iframe_src) ?>" target="_blank" style="font-size:.72rem;color:var(--accent);white-space:nowrap">↗ Tab</a>
        <a href="javascript:void(0)" onclick="refreshPreview(0)" style="font-size:.72rem;color:var(--accent)">↺</a>
      <?php else: ?>
        <span class="preview-url-label">/<?= h($slug) ?></span>
        <a href="/api/preview.php?page=<?= urlencode($slug) ?>" target="previewFrame" style="font-size:.72rem;color:var(--accent)">↺</a>
      <?php endif; ?>
    </div>

    <?php if ($use_live_preview): ?>
    <iframe id="previewFrame" name="previewFrame"
            src="<?= h($iframe_src) ?>"
            class="preview-frame" allow="autoplay"
            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"></iframe>
    <?php else: ?>
    <iframe id="previewFrame" name="previewFrame"
            src="/api/preview.php?page=<?= urlencode($slug) ?>"
            class="preview-frame" allow="autoplay"
            sandbox="allow-same-origin allow-scripts allow-popups"></iframe>
    <?php endif; ?>
  </div>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.6);align-items:center;justify-content:center;padding:20px">
  <div class="card" style="max-width:540px;width:100%;margin:0">
    <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
      Neue Sektion hinzufügen
      <button onclick="document.getElementById('addSectionModal').style.display='none'" style="background:none;border:none;color:var(--text-muted);font-size:1.2rem;cursor:pointer">&#10005;</button>
    </div>
    <form id="addSectionForm" onsubmit="createSection(event)">
      <div class="form-group">
        <label>Sektionstyp *</label>
        <select name="section_type" required style="width:100%">
          <option value="">— Typ auswählen —</option>
          <?php foreach (get_available_section_types() as $st_key => $st_label): ?>
          <option value="<?= h($st_key) ?>"><?= h($st_label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Bezeichnung *</label>
        <input type="text" name="label" placeholder="z.B. Unsere Vorteile" required>
      </div>
      <div class="form-group">
        <label>Schlüssel <span style="font-weight:400;color:var(--text-faint)">(optional)</span></label>
        <input type="text" name="section_key" placeholder="z.B. vorteile" pattern="[a-z0-9_]*">
        <div class="form-hint">Nur Kleinbuchstaben, Zahlen und Unterstriche</div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSectionModal').style.display='none'">Abbrechen</button>
        <button type="submit" class="btn btn-primary">Sektion erstellen</button>
      </div>
    </form>
  </div>
</div>

<!-- Live preview config for JS -->
<script>
var ZENTRA_PREVIEW_URL = <?= json_encode($reload_url) ?>;
var ZENTRA_USE_LIVE    = <?= $use_live_preview ? 'true' : 'false' ?>;
</script>

<!-- Inline accordion + CRUD script (no external dependency) -->
<script>
// Accordion toggle — bulletproof inline
function toggleAcc(hdr) {
  var body = hdr.nextElementSibling;
  if (!body) return;
  var isOpen = body.classList.contains('show');
  // Close all
  var allBodies = document.querySelectorAll('.acc-body.show');
  for (var i = 0; i < allBodies.length; i++) allBodies[i].classList.remove('show');
  var allHdrs = document.querySelectorAll('.acc-hdr.active');
  for (var i = 0; i < allHdrs.length; i++) allHdrs[i].classList.remove('active');
  // Open clicked if was closed
  if (!isOpen) {
    body.classList.add('show');
    hdr.classList.add('active');
  }
}

// Helper
function getCsrf() {
  var m = document.querySelector('meta[name="csrf"]');
  return m ? m.content : '';
}

// Delete section
async function deleteSection(id, label) {
  if (!confirm('Sektion "' + label + '" wirklich löschen?\n\nAlle Inhalte werden gelöscht.')) return;
  var fd = new FormData();
  fd.append('action', 'delete');
  fd.append('section_id', id);
  fd.append('csrf_token', getCsrf());
  try {
    var res = await fetch('/api/sections.php', { method: 'POST', body: fd });
    var json = await res.json();
    if (json.ok) {
      flash('Sektion gelöscht', 'ok');
      var el = document.querySelector('.section-accordion[data-section-id="' + id + '"]');
      if (el) el.remove();
      if (typeof refreshPreview === 'function') refreshPreview();
    } else {
      flash(json.error || 'Fehler', 'err');
    }
  } catch(e) { flash('Netzwerkfehler', 'err'); }
}

// Create section
async function createSection(e) {
  e.preventDefault();
  var fd = new FormData(e.target);
  fd.append('action', 'create');
  fd.append('page_id', document.getElementById('sectionsList').dataset.pageId);
  fd.append('csrf_token', getCsrf());
  try {
    var res = await fetch('/api/sections.php', { method: 'POST', body: fd });
    var json = await res.json();
    if (json.ok) {
      flash('Sektion erstellt', 'ok');
      setTimeout(function() { location.reload(); }, 600);
    } else {
      flash(json.error || 'Fehler', 'err');
    }
  } catch(e) { flash('Netzwerkfehler', 'err'); }
}
</script>

<!-- SortableJS CDN for drag-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
// Init SortableJS
(function() {
  var list = document.getElementById('sectionsList');
  if (!list || typeof Sortable === 'undefined') return;
  Sortable.create(list, {
    handle: '.section-drag-handle',
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    animation: 150,
    onStart: function() {
      var open = document.querySelectorAll('.acc-body.show');
      for (var i = 0; i < open.length; i++) open[i].classList.remove('show');
      var hdrs = document.querySelectorAll('.acc-hdr.active');
      for (var i = 0; i < hdrs.length; i++) hdrs[i].classList.remove('active');
    },
    onEnd: async function() {
      var items = list.querySelectorAll('.section-accordion');
      var order = [];
      for (var i = 0; i < items.length; i++) order.push(items[i].dataset.sectionId);
      var fd = new FormData();
      fd.append('action', 'reorder');
      fd.append('page_id', list.dataset.pageId);
      fd.append('order', JSON.stringify(order));
      fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');
      try {
        var res = await fetch('/api/sections.php', { method: 'POST', body: fd });
        var json = await res.json();
        if (json.ok) {
          flash('Reihenfolge gespeichert', 'ok');
          if (typeof refreshPreview === 'function') refreshPreview();
        } else { flash(json.error || 'Fehler', 'err'); }
      } catch(e) { flash('Netzwerkfehler', 'err'); }
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>
