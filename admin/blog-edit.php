<?php
// admin/blog-edit.php — Blog post editor (create / update)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$db         = db();

$post_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_new   = ($post_id === 0);
$post     = null;
$blocks   = [];

if (!$is_new) {
    $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ? AND project_id = ?');
    $stmt->execute([$post_id, $project_id]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: /admin/blog-list.php');
        exit;
    }
    $blocks = json_decode($post['content'] ?: '[]', true) ?: [];
}

$csrf      = csrf_token();
$page_title = $is_new ? 'Neuer Beitrag' : 'Beitrag bearbeiten';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<style>
/* ── Blog editor layout ── */
.blog-editor-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 900px) {
  .blog-editor-layout { grid-template-columns: 1fr; }
}

/* ── Block list ── */
#blockList { display: flex; flex-direction: column; gap: 8px; min-height: 40px; }

.block-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: var(--surface3);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 12px 14px;
  transition: border-color .15s;
}
.block-row:hover { border-color: var(--border-light); }
.block-row.sortable-ghost { opacity: .4; }
.block-row.sortable-chosen { border-color: var(--accent-border); }

.block-drag {
  cursor: grab;
  color: var(--text-faint);
  font-size: 1.1rem;
  line-height: 1;
  padding-top: 3px;
  flex-shrink: 0;
  user-select: none;
}
.block-drag:active { cursor: grabbing; }

.block-type-badge {
  font-size: .65rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  padding: 2px 7px;
  border-radius: 4px;
  flex-shrink: 0;
  margin-top: 4px;
}
.block-type-text    { background: var(--accent-soft);              color: var(--accent); }
.block-type-heading { background: var(--purple-bg);                color: var(--purple); }
.block-type-image   { background: var(--cyan-bg);                  color: var(--cyan); }
.block-type-video   { background: var(--pink-bg);                  color: var(--pink); }
.block-type-divider { background: rgba(255,255,255,.05);            color: var(--text-faint); }

.block-fields { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.block-fields input,
.block-fields textarea,
.block-fields select {
  width: 100%;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text);
  font-size: .82rem;
  padding: 7px 10px;
  font-family: inherit;
}
.block-fields textarea { resize: vertical; min-height: 80px; }
.block-fields input:focus,
.block-fields textarea:focus,
.block-fields select:focus {
  outline: none;
  border-color: var(--accent-border);
}
.block-fields .block-row-hint {
  font-size: .72rem;
  color: var(--text-faint);
  margin-top: -4px;
}
.block-fields .block-sub-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.block-delete-btn {
  background: none;
  border: none;
  color: var(--text-faint);
  cursor: pointer;
  font-size: 1rem;
  padding: 4px 6px;
  border-radius: 4px;
  line-height: 1;
  flex-shrink: 0;
  margin-top: 2px;
  transition: color .15s, background .15s;
}
.block-delete-btn:hover { color: var(--red); background: var(--red-bg); }

.divider-placeholder {
  text-align: center;
  font-size: .75rem;
  color: var(--text-faint);
  padding: 6px 0;
  border-top: 1px dashed var(--border);
  border-bottom: 1px dashed var(--border);
  margin: 2px 0;
}

/* ── Add block bar ── */
.add-block-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding: 10px 14px;
  background: var(--surface2);
  border: 1px dashed var(--border);
  border-radius: var(--r);
}
.add-block-bar select {
  background: var(--surface3);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text);
  font-size: .82rem;
  padding: 7px 10px;
  font-family: inherit;
  flex: 1;
}
.add-block-bar select:focus { outline: none; border-color: var(--accent-border); }

/* ── Sticky save bar ── */
.blog-save-bar {
  position: sticky;
  bottom: 0;
  background: var(--surface);
  border-top: 1px solid var(--border);
  padding: 14px 0;
  margin-top: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  z-index: 50;
}

/* ── Sidebar cards ── */
.sidebar-card { margin-bottom: 14px; }
.sidebar-card:last-child { margin-bottom: 0; }

/* ── Featured image preview ── */
.featured-img-preview {
  width: 100%;
  aspect-ratio: 16/9;
  object-fit: cover;
  border-radius: 6px;
  margin-top: 8px;
  border: 1px solid var(--border);
  display: none;
}

/* ── Char counter ── */
.char-counter {
  font-size: .7rem;
  color: var(--text-faint);
  text-align: right;
  margin-top: 3px;
}
.char-counter.over { color: var(--red); }

/* ── Author photo preview ── */
.author-photo-preview {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border);
  margin-top: 8px;
  display: none;
}
</style>

<div class="page-header">
  <div>
    <h1><?= $page_title ?></h1>
    <p><?= $is_new ? 'Neuen Blogbeitrag erstellen' : 'Beitrag bearbeiten · <a href="/admin/blog-list.php" style="color:var(--accent)">← Alle Beiträge</a>' ?></p>
  </div>
  <a href="/admin/blog-list.php" class="btn btn-secondary btn-sm">← Zurück</a>
</div>

<form id="blogForm" onsubmit="saveBlogPost(event)">
  <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="blog_post_id" value="<?= $post_id ?>">
  <input type="hidden" name="content_json" id="contentJsonInput" value="">

  <div class="blog-editor-layout">

    <!-- ══ LEFT COLUMN ══ -->
    <div>
      <!-- Inhalt-Editor -->
      <div class="card">
        <div class="card-title">Inhalt-Editor</div>

        <div class="form-group">
          <label>Titel <span style="color:var(--red)">*</span></label>
          <input type="text" name="title" id="postTitle" required
                 value="<?= h($post['title'] ?? '') ?>"
                 placeholder="Beitragstitel eingeben…"
                 style="font-size:1.05rem;font-weight:600">
        </div>

        <div class="form-group">
          <label>Slug <span style="color:var(--text-faint);font-weight:400;font-size:.78rem">(URL-Pfad)</span></label>
          <input type="text" name="slug" id="postSlug"
                 value="<?= h($post['slug'] ?? '') ?>"
                 placeholder="wird-automatisch-generiert"
                 pattern="[a-z0-9\-]*">
          <div class="form-hint">Nur Kleinbuchstaben, Zahlen und Bindestriche</div>
        </div>

        <!-- Block builder -->
        <div style="margin-top:4px">
          <label style="display:block;font-size:.78rem;font-weight:500;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.06em">Inhaltsblöcke</label>

          <div id="blockList">
            <?php foreach ($blocks as $i => $block): ?>
            <?php
              $type = $block['type'] ?? 'text';
              ob_start();
            ?>
            <div class="block-row" data-block-index="<?= $i ?>" data-block-type="<?= h($type) ?>">
              <span class="block-drag" title="Verschieben">⠿</span>
              <span class="block-type-badge block-type-<?= h($type) ?>"><?= h($type) ?></span>
              <div class="block-fields">
                <?php if ($type === 'text'): ?>
                  <textarea name="block[<?= $i ?>][value]" placeholder="HTML-Inhalt…"><?= h($block['value'] ?? '') ?></textarea>
                  <div class="block-row-hint">HTML erlaubt: &lt;p&gt; &lt;b&gt; &lt;i&gt; &lt;a&gt; &lt;ul&gt; &lt;li&gt; usw.</div>
                <?php elseif ($type === 'heading'): ?>
                  <div class="block-sub-row">
                    <input type="text" name="block[<?= $i ?>][text]" placeholder="Überschrift" value="<?= h($block['text'] ?? '') ?>">
                    <select name="block[<?= $i ?>][level]">
                      <option value="2" <?= ($block['level'] ?? 2) == 2 ? 'selected' : '' ?>>H2</option>
                      <option value="3" <?= ($block['level'] ?? 2) == 3 ? 'selected' : '' ?>>H3</option>
                    </select>
                  </div>
                <?php elseif ($type === 'image'): ?>
                  <div style="display:flex;gap:6px;align-items:flex-start">
                    <input type="text" id="block_<?= $i ?>_url" name="block[<?= $i ?>][url]"
                           placeholder="Bild-URL" value="<?= h($block['url'] ?? '') ?>" style="flex:1">
                    <button type="button" class="btn btn-secondary btn-sm media-picker-btn"
                            data-target="block_<?= $i ?>_url" style="flex-shrink:0;white-space:nowrap">Bild wählen</button>
                  </div>
                  <div class="block-sub-row">
                    <input type="text" name="block[<?= $i ?>][alt]" placeholder="Alt-Text" value="<?= h($block['alt'] ?? '') ?>">
                    <input type="text" name="block[<?= $i ?>][caption]" placeholder="Bildunterschrift (optional)" value="<?= h($block['caption'] ?? '') ?>">
                  </div>
                <?php elseif ($type === 'video'): ?>
                  <input type="text" name="block[<?= $i ?>][embed_url]"
                         placeholder="Embed-URL z.B. https://www.youtube.com/embed/…"
                         value="<?= h($block['embed_url'] ?? '') ?>">
                  <input type="text" name="block[<?= $i ?>][caption]" placeholder="Videotitel (optional)" value="<?= h($block['caption'] ?? '') ?>">
                <?php elseif ($type === 'divider'): ?>
                  <div class="divider-placeholder">— Trennlinie —</div>
                <?php endif; ?>
                <input type="hidden" name="block[<?= $i ?>][type]" value="<?= h($type) ?>">
              </div>
              <button type="button" class="block-delete-btn" onclick="removeBlock(this)" title="Block entfernen">✕</button>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Add block bar -->
          <div class="add-block-bar">
            <i data-lucide="plus-circle" style="width:16px;height:16px;color:var(--text-faint);flex-shrink:0"></i>
            <select id="newBlockType">
              <option value="text">Text</option>
              <option value="heading">Überschrift</option>
              <option value="image">Bild</option>
              <option value="video">Video</option>
              <option value="divider">Trennlinie</option>
            </select>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock()">Block hinzufügen</button>
          </div>
        </div>
      </div>

      <!-- Sticky save bar -->
      <div class="blog-save-bar">
        <span id="saveStatus" style="font-size:.8rem;color:var(--text-faint)"></span>
        <div style="display:flex;gap:10px">
          <a href="/admin/blog-list.php" class="btn btn-secondary">Abbrechen</a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i data-lucide="save" style="width:15px;height:15px;vertical-align:-2px;margin-right:5px"></i>Speichern
          </button>
        </div>
      </div>
    </div>

    <!-- ══ RIGHT SIDEBAR ══ -->
    <div>

      <!-- Status & Veröffentlichung -->
      <div class="card sidebar-card">
        <div class="card-title" style="font-size:.82rem">Status & Veröffentlichung</div>
        <div class="form-group" style="margin-bottom:10px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;margin-bottom:8px">
            <input type="radio" name="status" value="draft"
                   <?= (!$post || $post['status'] === 'draft') ? 'checked' : '' ?>
                   onchange="togglePublishedAt()"> Entwurf
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem">
            <input type="radio" name="status" value="published"
                   <?= ($post && $post['status'] === 'published') ? 'checked' : '' ?>
                   onchange="togglePublishedAt()"> Veröffentlicht
          </label>
        </div>
        <div id="publishedAtRow" style="display:<?= ($post && $post['status'] === 'published') ? 'block' : 'none' ?>">
          <div class="form-group" style="margin-bottom:0">
            <label style="font-size:.78rem">Datum & Uhrzeit</label>
            <input type="datetime-local" name="published_at"
                   value="<?= $post && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
          </div>
        </div>
      </div>

      <!-- Featured Image -->
      <div class="card sidebar-card">
        <div class="card-title" style="font-size:.82rem">Titelbild</div>
        <div class="form-group" style="margin-bottom:0">
          <div style="display:flex;gap:6px">
            <input type="text" name="featured_image" id="featuredImageInput"
                   placeholder="/uploads/…" style="flex:1;font-size:.8rem"
                   value="<?= h($post['featured_image'] ?? '') ?>"
                   oninput="updateFeaturedPreview(this.value)">
            <button type="button" class="btn btn-secondary btn-sm media-picker-btn"
                    data-target="featuredImageInput" style="flex-shrink:0;white-space:nowrap">Wählen</button>
          </div>
          <img id="featuredImgPreview" class="featured-img-preview"
               src="<?= h($post['featured_image'] ?? '') ?>"
               alt="Vorschau"
               style="<?= !empty($post['featured_image']) ? 'display:block' : 'display:none' ?>">
        </div>
      </div>

      <!-- Autor -->
      <div class="card sidebar-card">
        <div class="card-title" style="font-size:.82rem">Autor</div>
        <div class="form-group">
          <label style="font-size:.78rem">Name</label>
          <input type="text" name="author_name" placeholder="Max Mustermann"
                 value="<?= h($post['author_name'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label style="font-size:.78rem">Foto-URL</label>
          <div style="display:flex;gap:6px;align-items:flex-start">
            <input type="text" name="author_photo" id="authorPhotoInput"
                   placeholder="/uploads/…" style="flex:1;font-size:.8rem"
                   value="<?= h($post['author_photo'] ?? '') ?>"
                   oninput="updateAuthorPhotoPreview(this.value)">
            <button type="button" class="btn btn-secondary btn-sm media-picker-btn"
                    data-target="authorPhotoInput" style="flex-shrink:0;white-space:nowrap">Wählen</button>
          </div>
          <img id="authorPhotoPreview" class="author-photo-preview"
               src="<?= h($post['author_photo'] ?? '') ?>"
               alt="Autorenfoto"
               style="<?= !empty($post['author_photo']) ? 'display:block' : 'display:none' ?>">
        </div>
      </div>

      <!-- Kategorie & Meta -->
      <div class="card sidebar-card">
        <div class="card-title" style="font-size:.82rem">Kategorie & Meta</div>
        <div class="form-group">
          <label style="font-size:.78rem">Kategorie</label>
          <input type="text" name="category" placeholder="z.B. Tipps & Tricks"
                 value="<?= h($post['category'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label style="font-size:.78rem">Lesezeit (Minuten)</label>
          <input type="number" name="read_time" min="0" max="255"
                 placeholder="5"
                 value="<?= (int)($post['read_time'] ?? 0) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label style="font-size:.78rem">Kurzbeschreibung / Excerpt</label>
          <textarea name="excerpt" rows="3" placeholder="Kurze Zusammenfassung des Beitrags…"><?= h($post['excerpt'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- SEO -->
      <div class="card sidebar-card">
        <div class="card-title" style="font-size:.82rem">SEO</div>
        <div class="form-group">
          <label style="font-size:.78rem">SEO-Titel <span style="color:var(--text-faint);font-weight:400">(max. 70)</span></label>
          <input type="text" name="seo_title" id="seoTitleInput" maxlength="70"
                 placeholder="Beitragstitel für Google…"
                 value="<?= h($post['seo_title'] ?? '') ?>"
                 oninput="updateCounter('seoTitleInput','seoTitleCount',70)">
          <div class="char-counter" id="seoTitleCount">
            <?= mb_strlen($post['seo_title'] ?? '') ?> / 70
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label style="font-size:.78rem">Meta-Beschreibung <span style="color:var(--text-faint);font-weight:400">(max. 160)</span></label>
          <textarea name="seo_description" id="seoDescInput" maxlength="160" rows="3"
                    placeholder="Kurzbeschreibung für Suchergebnisse…"
                    oninput="updateCounter('seoDescInput','seoDescCount',160)"><?= h($post['seo_description'] ?? '') ?></textarea>
          <div class="char-counter" id="seoDescCount">
            <?= mb_strlen($post['seo_description'] ?? '') ?> / 160
          </div>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div><!-- /blog-editor-layout -->
</form>

<!-- SortableJS for drag-reorder blocks -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<script>
var BLOG_POST_ID = <?= $post_id ?>;
var BLOG_IS_NEW  = <?= $is_new ? 'true' : 'false' ?>;

// ── Slug auto-generation from title ──────────────────────────
(function() {
  var titleEl = document.getElementById('postTitle');
  var slugEl  = document.getElementById('postSlug');
  var slugEdited = <?= (!$is_new && !empty($post['slug'])) ? 'true' : 'false' ?>;

  titleEl.addEventListener('input', function() {
    if (slugEdited) return;
    slugEl.value = titleEl.value
      .toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/ä/g,'ae').replace(/ö/g,'oe').replace(/ü/g,'ue').replace(/ß/g,'ss')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  });

  slugEl.addEventListener('input', function() {
    slugEdited = true;
  });

  slugEl.addEventListener('blur', function() {
    slugEl.value = slugEl.value.toLowerCase().replace(/[^a-z0-9\-]/g, '').replace(/^-+|-+$/g, '');
  });
})();

// ── Status toggle (show/hide published_at) ───────────────────
function togglePublishedAt() {
  var pub = document.querySelector('input[name="status"][value="published"]').checked;
  document.getElementById('publishedAtRow').style.display = pub ? 'block' : 'none';
}

// ── Featured image preview ───────────────────────────────────
function updateFeaturedPreview(url) {
  var img = document.getElementById('featuredImgPreview');
  if (url) { img.src = url; img.style.display = 'block'; }
  else { img.style.display = 'none'; }
}

// ── Author photo preview ─────────────────────────────────────
function updateAuthorPhotoPreview(url) {
  var img = document.getElementById('authorPhotoPreview');
  if (url) { img.src = url; img.style.display = 'block'; }
  else { img.style.display = 'none'; }
}

// ── Char counter ─────────────────────────────────────────────
function updateCounter(inputId, counterId, max) {
  var el  = document.getElementById(inputId);
  var cnt = document.getElementById(counterId);
  var len = el.value.length;
  cnt.textContent = len + ' / ' + max;
  cnt.classList.toggle('over', len > max);
}

// ── Block management ─────────────────────────────────────────
var _blockIdx = <?= count($blocks) ?>;

function blockTypeHTML(type, idx) {
  var fields = '';
  if (type === 'text') {
    fields = '<textarea name="block[' + idx + '][value]" placeholder="HTML-Inhalt…"></textarea>'
           + '<div class="block-row-hint">HTML erlaubt: &lt;p&gt; &lt;b&gt; &lt;i&gt; &lt;a&gt; &lt;ul&gt; &lt;li&gt; usw.</div>';
  } else if (type === 'heading') {
    fields = '<div class="block-sub-row">'
           + '<input type="text" name="block[' + idx + '][text]" placeholder="Überschrift">'
           + '<select name="block[' + idx + '][level]"><option value="2">H2</option><option value="3">H3</option></select>'
           + '</div>';
  } else if (type === 'image') {
    fields = '<div style="display:flex;gap:6px;align-items:flex-start">'
           + '<input type="text" id="block_' + idx + '_url" name="block[' + idx + '][url]" placeholder="Bild-URL" style="flex:1">'
           + '<button type="button" class="btn btn-secondary btn-sm media-picker-btn" data-target="block_' + idx + '_url" style="flex-shrink:0;white-space:nowrap">Bild wählen</button>'
           + '</div>'
           + '<div class="block-sub-row">'
           + '<input type="text" name="block[' + idx + '][alt]" placeholder="Alt-Text">'
           + '<input type="text" name="block[' + idx + '][caption]" placeholder="Bildunterschrift (optional)">'
           + '</div>';
  } else if (type === 'video') {
    fields = '<input type="text" name="block[' + idx + '][embed_url]" placeholder="Embed-URL z.B. https://www.youtube.com/embed/…">'
           + '<input type="text" name="block[' + idx + '][caption]" placeholder="Videotitel (optional)">';
  } else if (type === 'divider') {
    fields = '<div class="divider-placeholder">— Trennlinie —</div>';
  }
  fields += '<input type="hidden" name="block[' + idx + '][type]" value="' + type + '">';
  return fields;
}

function addBlock() {
  var type = document.getElementById('newBlockType').value;
  var idx  = _blockIdx++;
  var row  = document.createElement('div');
  row.className = 'block-row';
  row.dataset.blockIndex = idx;
  row.dataset.blockType  = type;
  row.innerHTML =
    '<span class="block-drag" title="Verschieben">⠿</span>'
    + '<span class="block-type-badge block-type-' + type + '">' + type + '</span>'
    + '<div class="block-fields">' + blockTypeHTML(type, idx) + '</div>'
    + '<button type="button" class="block-delete-btn" onclick="removeBlock(this)" title="Block entfernen">✕</button>';
  document.getElementById('blockList').appendChild(row);
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removeBlock(btn) {
  var row = btn.closest('.block-row');
  if (row) row.remove();
}

// ── SortableJS init ──────────────────────────────────────────
(function() {
  var list = document.getElementById('blockList');
  if (typeof Sortable !== 'undefined') {
    Sortable.create(list, {
      handle: '.block-drag',
      animation: 150,
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen'
    });
  }
})();

// ── Serialize blocks to JSON ─────────────────────────────────
function serializeBlocks() {
  var rows   = document.querySelectorAll('#blockList .block-row');
  var blocks = [];
  rows.forEach(function(row) {
    var type = row.dataset.blockType;
    var block = { type: type };

    function fieldVal(selector) {
      var el = row.querySelector(selector);
      return el ? el.value : '';
    }

    if (type === 'text') {
      block.value = fieldVal('textarea');
    } else if (type === 'heading') {
      block.text  = fieldVal('input[name$="[text]"]');
      block.level = parseInt(fieldVal('select') || '2', 10);
    } else if (type === 'image') {
      block.url     = fieldVal('input[name$="[url]"]');
      block.alt     = fieldVal('input[name$="[alt]"]');
      block.caption = fieldVal('input[name$="[caption]"]');
    } else if (type === 'video') {
      block.embed_url = fieldVal('input[name$="[embed_url]"]');
      block.caption   = fieldVal('input[name$="[caption]"]');
    }
    // divider: no extra fields
    blocks.push(block);
  });
  return JSON.stringify(blocks);
}

// ── Save via fetch ───────────────────────────────────────────
async function saveBlogPost(e) {
  e.preventDefault();

  // Serialize blocks first
  document.getElementById('contentJsonInput').value = serializeBlocks();

  var saveBtn    = document.getElementById('saveBtn');
  var saveStatus = document.getElementById('saveStatus');
  saveBtn.disabled = true;
  saveBtn.textContent = 'Speichert…';
  saveStatus.textContent = '';

  var fd = new FormData(document.getElementById('blogForm'));

  try {
    var res  = await fetch('/api/blog-manage.php', { method: 'POST', body: fd });
    var json = await res.json();

    if (json.ok) {
      flash('Beitrag gespeichert ✓', 'ok');
      if (json.redirect) {
        setTimeout(function() { window.location.href = json.redirect; }, 400);
      } else {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i data-lucide="save" style="width:15px;height:15px;vertical-align:-2px;margin-right:5px"></i>Speichern';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        saveStatus.textContent = 'Gespeichert';
        setTimeout(function() { saveStatus.textContent = ''; }, 3000);
      }
    } else {
      flash(json.error || 'Fehler beim Speichern', 'err');
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i data-lucide="save" style="width:15px;height:15px;vertical-align:-2px;margin-right:5px"></i>Speichern';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }
  } catch (err) {
    flash('Netzwerkfehler', 'err');
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i data-lucide="save" style="width:15px;height:15px;vertical-align:-2px;margin-right:5px"></i>Speichern';
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}

// ── Media picker hook (integrates with existing admin.js) ────
// admin.js listens for clicks on .media-picker-btn and uses data-target
// to fill the input after a media is chosen. Preview updates fire via
// oninput handlers already bound on inputs above.
// For block image inputs added dynamically, we need to patch the preview
// after admin.js fills the field. The input[oninput] approach doesn't
// fire when set programmatically — use a MutationObserver fallback.
(function() {
  document.addEventListener('mediaSelected', function(e) {
    var url = e.detail && e.detail.url;
    if (!url) return;
    var target = e.detail.target;
    // Check if featured image or author photo
    if (target === 'featuredImageInput') updateFeaturedPreview(url);
    if (target === 'authorPhotoInput')   updateAuthorPhotoPreview(url);
  });
})();
</script>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>
