/* ===== Zentra Admin UI — admin.js ===== */
'use strict';

// ── Flash messages ──────────────────────────────────────────────
function flash(msg, type = 'ok') {
  const el = document.createElement('div');
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:260px';
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .3s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 300);
  }, 3200);
}

// ── Accordion ───────────────────────────────────────────────────
document.querySelectorAll('.section-accordion-header').forEach(hdr => {
  hdr.addEventListener('click', () => {
    const body = hdr.nextElementSibling;
    const isOpen = body.classList.contains('open');
    // Close all
    document.querySelectorAll('.section-accordion-body.open').forEach(b => b.classList.remove('open'));
    document.querySelectorAll('.section-accordion-header.open').forEach(h => h.classList.remove('open'));
    if (!isOpen) {
      body.classList.add('open');
      hdr.classList.add('open');
    }
  });
});

// ── Preview iframe refresh ──────────────────────────────────────
let previewDebounce = null;
function refreshPreview(delayMs) {
  clearTimeout(previewDebounce);
  // Live-Preview: reload the live website URL with a cache-buster after the webhook has fired.
  // Fallback: reload the internal preview template.
  // 1500ms for live (webhook round-trip takes ~500-1000ms); 800ms for internal template.
  const delay = (typeof delayMs === 'number') ? delayMs : (typeof ZENTRA_USE_LIVE !== 'undefined' && ZENTRA_USE_LIVE ? 1500 : 800);
  previewDebounce = setTimeout(() => {
    const frame = document.getElementById('previewFrame');
    if (!frame) return;
    if (typeof ZENTRA_PREVIEW_URL !== 'undefined' && ZENTRA_PREVIEW_URL) {
      // Append cache-buster so the browser reloads after webhook has updated content.json
      const url = new URL(ZENTRA_PREVIEW_URL, window.location.href);
      url.searchParams.set('_cb', Date.now());
      frame.src = url.toString();
    } else {
      frame.src = frame.src;
    }
  }, delay);
}

// ── Last-saved indicator ────────────────────────────────────────
let lastSavedAt = null;
let lastSavedTimer = null;

function updateLastSavedDisplay() {
  const bar = document.getElementById('lastSavedBar');
  if (!bar || !lastSavedAt) return;
  const secs = Math.round((Date.now() - lastSavedAt) / 1000);
  let txt;
  if (secs < 5)    txt = 'Gerade gespeichert';
  else if (secs < 60) txt = `Vor ${secs}s gespeichert`;
  else if (secs < 3600) txt = `Vor ${Math.round(secs/60)}min gespeichert`;
  else               txt = `Vor ${Math.round(secs/3600)}h gespeichert`;
  bar.textContent = txt;
  if (secs < 5) bar.classList.add('just-saved');
  else          bar.classList.remove('just-saved');
}

function markSaved() {
  lastSavedAt = Date.now();
  updateLastSavedDisplay();
  // Clear all unsaved dots
  document.querySelectorAll('.unsaved-dot.visible').forEach(d => d.classList.remove('visible'));
  clearInterval(lastSavedTimer);
  lastSavedTimer = setInterval(updateLastSavedDisplay, 10000);
}

// ── Unsaved changes tracking ────────────────────────────────────
function markUnsaved(form) {
  const sec = form.closest('.section-accordion');
  if (!sec) return;
  const dot = sec.querySelector('.unsaved-dot');
  if (dot) dot.classList.add('visible');
}

// ── Auto-save section form ──────────────────────────────────────
document.querySelectorAll('.section-form').forEach(form => {
  // Track changes within the form
  form.addEventListener('input', () => markUnsaved(form));
  form.addEventListener('change', () => markUnsaved(form));

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    const orig = btn.textContent;
    btn.textContent = 'Speichern…';
    btn.disabled = true;

    const data = new FormData(form);
    // Auto-signal repeater intent: api/save.php needs to know if items should be cleared
    // when the user deletes all rows. Without this, absent items[] = "don't touch".
    const hasRepeater = form.querySelector('[name^="items["]')
        || form.querySelector('.repeater-add')
        || form.querySelector('[id$="Repeater"]');
    if (hasRepeater && !data.has('items_submitted')) {
      data.append('items_submitted', '1');
    }
    try {
      const res = await fetch('/api/save.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.ok) {
        flash('Gespeichert ✓', 'ok');
        markSaved();
        refreshPreview();
      } else {
        flash(json.error || 'Fehler beim Speichern', 'err');
      }
    } catch (err) {
      flash('Netzwerkfehler', 'err');
    } finally {
      btn.textContent = orig;
      btn.disabled = false;
    }
  });
});

// ── SEO form save ───────────────────────────────────────────────
document.querySelectorAll('.seo-form').forEach(form => {
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    const orig = btn.textContent;
    btn.textContent = 'Speichern…';
    btn.disabled = true;

    const data = new FormData(form);
    try {
      const res = await fetch('/api/save-seo.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.ok) flash('SEO gespeichert ✓', 'ok');
      else flash(json.error || 'Fehler', 'err');
    } catch { flash('Netzwerkfehler', 'err'); }
    finally { btn.textContent = orig; btn.disabled = false; }
  });
});

// ── Settings form save ──────────────────────────────────────────
const settingsForm = document.getElementById('settingsForm');
if (settingsForm) {
  settingsForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = settingsForm.querySelector('[type="submit"]');
    const orig = btn.textContent;
    btn.textContent = 'Speichern…';
    btn.disabled = true;

    const data = new FormData(settingsForm);
    try {
      const res = await fetch('/api/save-settings.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.ok) flash('Einstellungen gespeichert ✓', 'ok');
      else flash(json.error || 'Fehler', 'err');
    } catch { flash('Netzwerkfehler', 'err'); }
    finally { btn.textContent = orig; btn.disabled = false; }
  });
}

// ── Repeater rows ───────────────────────────────────────────────
// Helper: clone a repeater row, clear values, update indices
function _cloneRepeaterRow(template, idx) {
  const clone = template.cloneNode(true);
  // Clear all input values
  clone.querySelectorAll('input, textarea, select').forEach(inp => {
    if (inp.type === 'checkbox') inp.checked = false;
    else inp.value = '';
  });
  // Remove stale image previews
  clone.querySelectorAll('.image-field-preview img, .image-field-preview button').forEach(el => el.remove());
  // Reset header label
  const hdr = clone.querySelector('.repeater-row-header');
  if (hdr) {
    const firstText = hdr.childNodes[0];
    if (firstText && firstText.nodeType === 3) firstText.textContent = 'Neuer Eintrag ';
    const strong = hdr.querySelector('strong');
    if (strong) strong.textContent = 'Neuer Eintrag';
  }
  // Re-index: replace the item index in name="items[N][field]"
  clone.querySelectorAll('[name]').forEach(el => {
    el.name = el.name.replace(/^(items\[)\d+(\].*)$/, `$1${idx}$2`);
  });
  // Give URL inputs fresh IDs so media picker works
  clone.querySelectorAll('input[type="url"]').forEach(inp => {
    if (inp.id) {
      const newId = 'auto_r_' + idx + '_' + Math.random().toString(36).slice(2, 6);
      const preview = clone.querySelector('#' + CSS.escape(inp.id) + '_preview');
      const pickBtn = clone.querySelector('[data-target="' + inp.id + '"]');
      const clearBtn = clone.querySelector('[data-target="' + inp.id + '"]');
      if (preview) preview.id = newId + '_preview';
      if (pickBtn && pickBtn.classList.contains('media-picker-btn')) pickBtn.dataset.target = newId;
      if (clearBtn && clearBtn.classList.contains('image-field-clear')) clearBtn.dataset.target = newId;
      inp.id = newId;
      inp.dataset.pickerInit = '';
    }
  });
  return clone;
}

document.querySelectorAll('.repeater-add').forEach(btn => {
  // Lazily capture template from last row (updated each time a row exists)
  const _getContainer = () => btn.previousElementSibling;
  // Store a deep clone at page-load time if rows exist
  const initRow = _getContainer()?.querySelector('.repeater-row:last-child');
  if (initRow) btn._repeaterTemplate = initRow.cloneNode(true);

  btn.addEventListener('click', () => {
    const container = _getContainer();
    const lastRow   = container.querySelector('.repeater-row:last-child');
    // Always prefer a live row as template source (freshest structure)
    if (lastRow) btn._repeaterTemplate = lastRow.cloneNode(true);
    if (!btn._repeaterTemplate) {
      flash('Bitte erst einen Eintrag über die Seite befüllen und speichern.', 'err');
      return;
    }
    const idx   = container.querySelectorAll('.repeater-row').length;
    const clone = _cloneRepeaterRow(btn._repeaterTemplate, idx);
    container.appendChild(clone);
    // Init any new URL inputs for the media picker
    if (typeof initAll === 'function') initAll();
  });
});

document.addEventListener('click', e => {
  if (e.target.closest('.repeater-remove')) {
    const row = e.target.closest('.repeater-row');
    if (row) row.remove();
  }
});

// ── Image upload zone ───────────────────────────────────────────
document.querySelectorAll('.upload-zone').forEach(zone => {
  const inp = zone.querySelector('input[type="file"]');

  zone.addEventListener('click', () => inp && inp.click());
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    if (e.dataTransfer.files.length) handleUpload(e.dataTransfer.files[0], zone);
  });

  if (inp) {
    inp.addEventListener('change', () => {
      if (inp.files.length) handleUpload(inp.files[0], zone);
    });
  }
});

async function handleUpload(file, zone) {
  const allowed = ['image/jpeg','image/png','image/webp','image/gif','image/svg+xml'];
  if (!allowed.includes(file.type)) { flash('Ungültiger Dateityp', 'err'); return; }
  if (file.size > 10 * 1024 * 1024) { flash('Datei zu groß (max 10 MB)', 'err'); return; }

  const fd = new FormData();
  fd.append('file', file);
  fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');
  fd.append('project_id', document.querySelector('meta[name="project_id"]')?.content || '');

  zone.querySelector('p').textContent = 'Wird hochgeladen…';
  try {
    const res = await fetch('/api/upload.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      flash('Bild hochgeladen ✓', 'ok');
      zone.querySelector('p').textContent = json.url;
      // If there's a target field next to the zone, fill it
      const target = zone.dataset.target;
      if (target) {
        const field = document.querySelector(`[name="${target}"]`);
        if (field) field.value = json.url;
      }
      // Reload media grid if on media page
      if (typeof reloadMediaGrid === 'function') reloadMediaGrid();
    } else {
      flash(json.error || 'Upload fehlgeschlagen', 'err');
      zone.querySelector('p').textContent = 'Datei hierher ziehen oder klicken';
    }
  } catch {
    flash('Netzwerkfehler beim Upload', 'err');
    zone.querySelector('p').textContent = 'Datei hierher ziehen oder klicken';
  }
}

// ── Copy to clipboard ───────────────────────────────────────────
document.addEventListener('click', async e => {
  const btn = e.target.closest('[data-copy]');
  if (!btn) return;
  try {
    await navigator.clipboard.writeText(btn.dataset.copy);
    const orig = btn.textContent;
    btn.textContent = 'Kopiert!';
    setTimeout(() => btn.textContent = orig, 1500);
  } catch { flash('Kopieren fehlgeschlagen', 'err'); }
});

// ── Toggle section enabled ──────────────────────────────────────
document.querySelectorAll('.section-toggle').forEach(tog => {
  tog.addEventListener('change', async function(e) {
    e.stopPropagation();
    const fd = new FormData();
    fd.append('section_id', this.dataset.sectionId);
    fd.append('enabled', this.checked ? '1' : '0');
    fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');
    try {
      const res = await fetch('/api/save.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (!json.ok) { this.checked = !this.checked; flash('Fehler', 'err'); }
      else refreshPreview();
    } catch { this.checked = !this.checked; flash('Netzwerkfehler', 'err'); }
  });
  // Prevent toggle clicks from triggering accordion
  tog.closest('label')?.addEventListener('click', function(e) { e.stopPropagation(); });
});

// ── Sidebar mobile toggle ───────────────────────────────────────
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', e => {
    if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
      sidebar.classList.remove('open');
    }
  });
}

// ── Media Picker Modal ─────────────────────────────────────────
(function() {
  // Create modal DOM once
  const overlay = document.createElement('div');
  overlay.className = 'media-picker-overlay';
  overlay.innerHTML = `
    <div class="media-picker-modal">
      <div class="media-picker-header">
        <h3>Bild auswählen</h3>
        <button class="media-picker-close" type="button">&times;</button>
      </div>
      <div class="media-picker-body">
        <div class="media-picker-upload" id="mpUploadZone">
          <input type="file" accept="image/*" style="display:none" id="mpFileInput">
          Neues Bild hochladen — hierher ziehen oder klicken
        </div>
        <div class="media-picker-grid" id="mpGrid"></div>
      </div>
    </div>`;
  document.body.appendChild(overlay);

  let activeTargetId = null;

  // Close
  overlay.querySelector('.media-picker-close').addEventListener('click', closePicker);
  overlay.addEventListener('click', e => { if (e.target === overlay) closePicker(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && overlay.classList.contains('open')) closePicker(); });

  function closePicker() {
    overlay.classList.remove('open');
    activeTargetId = null;
  }

  // Open picker
  document.addEventListener('click', e => {
    const btn = e.target.closest('.media-picker-btn');
    if (!btn) return;
    activeTargetId = btn.dataset.target;
    overlay.classList.add('open');
    loadMedia();
  });

  // Load media grid
  async function loadMedia() {
    const grid = document.getElementById('mpGrid');
    grid.innerHTML = '<div class="media-picker-empty">Lade...</div>';
    try {
      const res = await fetch('/api/media-list.php');
      const json = await res.json();
      if (!json.ok || !json.media.length) {
        grid.innerHTML = '<div class="media-picker-empty">Noch keine Bilder vorhanden. Lade eins hoch!</div>';
        return;
      }
      grid.innerHTML = '';
      json.media.forEach(m => {
        const item = document.createElement('div');
        item.className = 'media-picker-item';
        item.innerHTML = `<img src="${escHtml(m.url)}" alt="${escHtml(m.alt_text || m.filename)}" loading="lazy">
          <div class="media-picker-item-name">${escHtml(m.filename)}</div>`;
        item.addEventListener('click', () => selectImage(m.url));
        grid.appendChild(item);
      });
    } catch {
      grid.innerHTML = '<div class="media-picker-empty">Fehler beim Laden</div>';
    }
  }

  // Select image
  function selectImage(url) {
    if (!activeTargetId) return;
    const input = document.getElementById(activeTargetId);
    if (input) {
      input.value = url;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    // Update preview
    const preview = document.getElementById(activeTargetId + '_preview');
    if (preview) {
      preview.innerHTML = `<img src="${escHtml(url)}" alt="">
        <button type="button" class="image-field-clear" data-target="${activeTargetId}" title="Bild entfernen">&times;</button>`;
    }
    closePicker();
  }

  // Upload in picker
  const uploadZone = document.getElementById('mpUploadZone');
  const fileInput = document.getElementById('mpFileInput');

  uploadZone.addEventListener('click', () => fileInput.click());
  uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.style.borderColor = 'var(--accent)'; });
  uploadZone.addEventListener('dragleave', () => { uploadZone.style.borderColor = ''; });
  uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.style.borderColor = '';
    if (e.dataTransfer.files.length) pickerUpload(e.dataTransfer.files[0]);
  });
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) pickerUpload(fileInput.files[0]);
    fileInput.value = '';
  });

  async function pickerUpload(file) {
    const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!allowed.includes(file.type)) { flash('Ungültiger Dateityp', 'err'); return; }
    if (file.size > 10 * 1024 * 1024) { flash('Datei zu groß (max 10 MB)', 'err'); return; }

    uploadZone.textContent = 'Wird hochgeladen…';
    const fd = new FormData();
    fd.append('file', file);
    fd.append('csrf_token', document.querySelector('meta[name="csrf"]')?.content || '');
    fd.append('project_id', document.querySelector('meta[name="project_id"]')?.content || '');

    try {
      const res = await fetch('/api/upload.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.ok) {
        flash('Bild hochgeladen', 'ok');
        selectImage(json.url);
        // Also reload the media page grid if open
        if (typeof reloadMediaGrid === 'function') reloadMediaGrid();
      } else {
        flash(json.error || 'Upload fehlgeschlagen', 'err');
      }
    } catch {
      flash('Netzwerkfehler', 'err');
    }
    uploadZone.innerHTML = '<input type="file" accept="image/*" style="display:none" id="mpFileInput">Neues Bild hochladen — hierher ziehen oder klicken';
    // Re-bind file input after innerHTML reset
    document.getElementById('mpFileInput')?.addEventListener('change', function() {
      if (this.files.length) pickerUpload(this.files[0]);
      this.value = '';
    });
  }

  // Clear image button (delegated)
  document.addEventListener('click', e => {
    const btn = e.target.closest('.image-field-clear');
    if (!btn) return;
    const targetId = btn.dataset.target;
    const input = document.getElementById(targetId);
    if (input) { input.value = ''; input.dispatchEvent(new Event('change', { bubbles: true })); }
    const preview = document.getElementById(targetId + '_preview');
    if (preview) preview.innerHTML = '';
  });

  function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }
})();

// ── Global Auto Media Picker ────────────────────────────────────
// Automatically wraps every <input type="url"> inside .section-form
// with the "Bild wählen" button + preview. Works for existing fields
// AND dynamically added repeater rows. No PHP changes needed.
(function() {
  let autoIdCounter = 0;

  function initImagePicker(input) {
    // Skip if already initialized or not a URL input
    if (input.dataset.pickerInit || input.type !== 'url') return;
    // Skip if already inside a .image-field-wrap (manually coded)
    if (input.closest('.image-field-wrap')) { input.dataset.pickerInit = '1'; return; }
    // Only target inputs inside section forms
    if (!input.closest('.section-form')) return;

    input.dataset.pickerInit = '1';

    // Ensure unique ID
    if (!input.id) input.id = 'auto_img_' + (++autoIdCounter) + '_' + Math.random().toString(36).slice(2,6);
    input.classList.add('image-field-input');

    // Wrap input
    const wrap = document.createElement('div');
    wrap.className = 'image-field-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    // Add picker button
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-secondary btn-sm media-picker-btn';
    btn.dataset.target = input.id;
    btn.textContent = 'Bild wählen';
    wrap.appendChild(btn);

    // Add preview div
    const preview = document.createElement('div');
    preview.className = 'image-field-preview';
    preview.id = input.id + '_preview';
    if (input.value) {
      preview.innerHTML = '<img src="' + _escAttr(input.value) + '" alt="">'
        + '<button type="button" class="image-field-clear" data-target="' + input.id + '" title="Bild entfernen">&times;</button>';
    }
    wrap.parentNode.insertBefore(preview, wrap.nextSibling);
  }

  function _escAttr(s) {
    const d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }

  // Init all existing URL inputs
  function initAll() {
    document.querySelectorAll('.section-form input[type="url"]').forEach(initImagePicker);
  }

  // Run on DOM ready
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
  else initAll();

  // Watch for dynamically added repeater rows
  const observer = new MutationObserver(mutations => {
    for (const m of mutations) {
      for (const node of m.addedNodes) {
        if (node.nodeType !== 1) continue;
        if (node.matches && node.matches('input[type="url"]')) initImagePicker(node);
        if (node.querySelectorAll) node.querySelectorAll('input[type="url"]').forEach(initImagePicker);
      }
    }
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

// ── Ctrl+S shortcut ─────────────────────────────────────────────
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    // Find the currently open section form and submit it
    const openBody = document.querySelector('.acc-body.show');
    if (openBody) {
      const form = openBody.querySelector('.section-form');
      if (form) {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        return;
      }
    }
    // Fallback: submit the first visible form on page
    const form = document.querySelector('form[id="settingsForm"]') ||
                 document.querySelector('.section-form');
    if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
  }
});

// ── Device preview toggle ───────────────────────────────────────
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.device-btn');
  if (!btn) return;
  const frame = document.getElementById('previewFrame');
  if (!frame) return;
  const device = btn.dataset.device;
  // Update active state
  btn.closest('.device-toggle')?.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  // Resize the preview wrapper
  const wrapper = frame.parentElement;
  if (device === 'desktop') {
    frame.style.width = '100%';
    frame.style.margin = '0';
    frame.style.borderRadius = '';
  } else if (device === 'tablet') {
    frame.style.width = '768px';
    frame.style.margin = '0 auto';
    frame.style.borderRadius = '12px';
  } else if (device === 'mobile') {
    frame.style.width = '390px';
    frame.style.margin = '0 auto';
    frame.style.borderRadius = '20px';
  }
});

// ── Collapse / Expand all sections ─────────────────────────────
document.addEventListener('click', function(e) {
  if (e.target.closest('[data-collapse-all]')) {
    document.querySelectorAll('.acc-body.show').forEach(b => b.classList.remove('show'));
    document.querySelectorAll('.acc-hdr.active').forEach(h => h.classList.remove('active'));
  }
  if (e.target.closest('[data-expand-all]')) {
    document.querySelectorAll('.acc-body').forEach(b => b.classList.add('show'));
    document.querySelectorAll('.acc-hdr').forEach(h => h.classList.add('active'));
  }
});

// ── Stat counter animation ─────────────────────────────────────
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseFloat(el.dataset.count);
    if (isNaN(target) || el._counted) return;
    el._counted = true;
    const isInt = Number.isInteger(target);
    const duration = 1200;
    const start = performance.now();
    function tick(now) {
      const t = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - t, 3); // easeOutCubic
      const val = target * ease;
      el.textContent = isInt ? Math.round(val).toLocaleString('de-DE') : val.toFixed(1);
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });
}
// Run after DOM is ready
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', animateCounters);
else animateCounters();
