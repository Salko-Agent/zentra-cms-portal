<?php
// admin/settings.php — site-wide settings
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

auth_start();
auth_check();

$project_id = (int)$_SESSION['project_id'];
$s = get_settings($project_id);

// Load domain directly from projects table
$db = db();
$proj_row = $db->prepare('SELECT domain FROM projects WHERE id = ?');
$proj_row->execute([$project_id]);
$proj = $proj_row->fetch();
$project_domain = $proj['domain'] ?? '';

$page_title = 'Einstellungen';
require_once __DIR__ . '/../partials/admin-header.php';
?>

<div class="page-header">
  <div><h1>Einstellungen</h1><p>Website-weite Stammdaten</p></div>
</div>

<form id="settingsForm" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
  <input type="hidden" name="project_id" value="<?= $project_id ?>">

  <div class="card">
    <div class="card-title">Allgemein</div>
    <div class="form-row">
      <div class="form-group">
        <label>Studio-Name</label>
        <input type="text" name="site_name" value="<?= htmlspecialchars($s['site_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Tagline</label>
        <input type="text" name="site_tagline" value="<?= htmlspecialchars($s['site_tagline'] ?? '') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Telefon</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($s['phone'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>E-Mail</label>
        <input type="email" name="email" value="<?= htmlspecialchars($s['email'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Adresse</label>
      <input type="text" name="address" value="<?= htmlspecialchars($s['address'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Google Maps URL</label>
      <input type="url" name="maps_url" value="<?= htmlspecialchars($s['maps_url'] ?? '') ?>">
    </div>
  </div>

  <div class="card">
    <div class="card-title">Social Media</div>
    <div class="form-row">
      <div class="form-group">
        <label>Instagram URL</label>
        <input type="url" name="instagram_url" value="<?= htmlspecialchars($s['instagram_url'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Facebook URL</label>
        <input type="url" name="facebook_url" value="<?= htmlspecialchars($s['facebook_url'] ?? '') ?>">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Tracking &amp; Analytics</div>
    <div class="form-row">
      <div class="form-group">
        <label>Google Analytics ID (GA4)</label>
        <input type="text" name="ga_id" value="<?= htmlspecialchars($s['ga_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
      </div>
      <div class="form-group">
        <label>GTM Container ID</label>
        <input type="text" name="gtm_id" value="<?= htmlspecialchars($s['gtm_id'] ?? '') ?>" placeholder="GTM-XXXXXX">
      </div>
    </div>
    <div class="form-group">
      <label>Meta Pixel ID</label>
      <input type="text" name="meta_pixel_id" value="<?= htmlspecialchars($s['meta_pixel_id'] ?? '') ?>">
    </div>
  </div>

  <div class="card">
    <div class="card-title">Custom Scripts</div>
    <div class="form-group">
      <label>Custom &lt;head&gt; Scripts</label>
      <textarea name="head_custom_scripts" rows="5" style="font-family:monospace;font-size:.82rem"><?= htmlspecialchars($s['head_custom_scripts'] ?? '') ?></textarea>
      <div class="form-hint">Wird direkt vor &lt;/head&gt; eingefügt — z.B. zusätzliche Tracking-Codes, Schriftarten, Custom CSS.</div>
    </div>
    <div class="form-group">
      <label>Custom &lt;body&gt; Scripts</label>
      <textarea name="body_custom_scripts" rows="5" style="font-family:monospace;font-size:.82rem"><?= htmlspecialchars($s['body_custom_scripts'] ?? '') ?></textarea>
      <div class="form-hint">Wird direkt nach &lt;body&gt; eingefügt — z.B. GTM noscript, Chat-Widgets, Live-Support-Codes.</div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Webhook &amp; API</div>
    <div class="form-group">
      <label>Website Domain (für Google Indexierung)</label>
      <input type="url" name="project_domain" value="<?= htmlspecialchars($project_domain) ?>"
             placeholder="https://flexfit-demo.at">
      <div class="form-hint">Basis-URL der Website — z.B. <code>https://flexfit-demo.at</code>. Wird für den "Google indexieren"-Button verwendet.</div>
    </div>
    <div class="form-group">
      <label>Live-Vorschau URL</label>
      <input type="url" name="preview_url" value="<?= htmlspecialchars($s['preview_url'] ?? '') ?>"
             placeholder="https://flexfit-demo.at">
      <div class="form-hint">
        Basis-URL der <strong>live geschalteten Website</strong> — wird im CMS als Vorschau angezeigt statt der Template-Preview.<br>
        Leer lassen = CMS zeigt weiterhin die eingebaute Template-Vorschau.<br>
        Voraussetzung: Die Live-Seite muss die Zentra Live-Preview-Unterstützung enthalten (<code>?_preview=TOKEN</code>).
      </div>
    </div>
    <div class="form-group">
      <label>Client Webhook URL</label>
      <input type="url" name="webhook_url" value="<?= htmlspecialchars($s['webhook_url'] ?? '') ?>"
             placeholder="https://flexfit-demo.at/_webhook.php">
      <div class="form-hint">Wird nach jeder Speicherung aufgerufen, um den Cache der Website zu leeren.</div>
    </div>
    <div class="form-group">
      <label>Client Upload-Receiver URL</label>
      <input type="url" name="upload_receiver_url" value="<?= htmlspecialchars($s['upload_receiver_url'] ?? '') ?>"
             placeholder="https://flexfit-demo.at/_upload-receive.php">
    </div>
    <div style="margin-top:12px;display:flex;align-items:center;gap:12px">
      <button type="button" class="btn btn-secondary btn-sm" onclick="syncWebhook()">
        ↻ Jetzt synchronisieren
      </button>
      <span id="syncResult" style="font-size:.85rem"></span>
    </div>
    <div class="form-hint" style="margin-top:6px">Sendet alle aktuellen Inhalte sofort an die Website — z.B. nach manuellen Datenbank-Änderungen.</div>
  </div>

  <div class="card">
    <div class="card-title">Google API Integration</div>
    <div class="form-hint" style="margin-bottom:16px">
      Verbinde Google Analytics und Search Console um Daten im Analytics-Dashboard zu sehen.
      Service Account: <code>zentra-seo@zentracmsanalytics.iam.gserviceaccount.com</code>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>GA4 Property ID</label>
        <input type="text" name="ga4_property_id" value="<?= htmlspecialchars($s['ga4_property_id'] ?? '') ?>"
               placeholder="properties/123456789">
        <div class="form-hint">Aus Google Analytics → Verwaltung → Property-Details</div>
      </div>
      <div class="form-group">
        <label>Search Console Property</label>
        <input type="text" name="gsc_property" value="<?= htmlspecialchars($s['gsc_property'] ?? '') ?>"
               placeholder="https://www.domain.at/ oder sc-domain:domain.at">
        <div class="form-hint">Exakt wie in Search Console angezeigt</div>
      </div>
    </div>
    <div class="form-group">
      <label>PageSpeed API Key (optional)</label>
      <input type="text" name="pagespeed_api_key" value="<?= htmlspecialchars($s['pagespeed_api_key'] ?? '') ?>"
             placeholder="AIza...">
      <div class="form-hint">Überschreibt den globalen API Key. Leer = Standard-Key verwenden.</div>
    </div>
    <div style="margin-top:12px">
      <button type="button" class="btn btn-secondary btn-sm" onclick="testGoogleConnection()">
        Verbindung testen
      </button>
      <span id="googleTestResult" style="margin-left:12px;font-size:.85rem"></span>
    </div>
  </div>

  <div class="save-bar">
    <button type="submit" class="btn btn-primary">Einstellungen speichern</button>
  </div>
</form>

<?php require_once __DIR__ . '/../partials/admin-footer.php'; ?>

<script>
function testGoogleConnection() {
  const el = document.getElementById('googleTestResult');
  el.textContent = 'Teste...';
  el.style.color = 'var(--text-muted)';
  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
  const fd = new FormData();
  fd.append('csrf_token', csrf);
  fetch('/api/test-google.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (!d.ok) {
        el.textContent = d.error || 'Fehler';
        el.style.color = 'var(--red, #ef4444)';
        return;
      }
      const r = d.results;
      const parts = [];
      if (r.ga4 === 'ok') parts.push('GA4 ✓');
      else if (r.ga4 === 'not_configured') parts.push('GA4: nicht konfiguriert');
      else parts.push('GA4 ✗');
      if (r.gsc === 'ok') parts.push('GSC ✓');
      else if (r.gsc === 'not_configured') parts.push('GSC: nicht konfiguriert');
      else parts.push('GSC ✗' + (r.gsc_detail ? ' (' + r.gsc_detail + ')' : ''));
      el.textContent = parts.join(' · ');
      el.style.color = (r.ga4 === 'ok' || r.gsc === 'ok') ? 'var(--green, #22c55e)' : 'var(--gold, #eab308)';
    })
    .catch(() => { el.textContent = 'Verbindungsfehler'; el.style.color = 'var(--red, #ef4444)'; });
}

function syncWebhook() {
  const el = document.getElementById('syncResult');
  el.textContent = 'Synchronisiere…';
  el.style.color = 'var(--text-muted)';
  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
  fetch('/api/sync.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ csrf_token: csrf })
  })
    .then(r => r.json())
    .then(d => {
      el.textContent = d.ok ? '✓ Synchronisiert' : ('Fehler: ' + (d.error || 'unbekannt'));
      el.style.color = d.ok ? 'var(--green, #22c55e)' : 'var(--red, #ef4444)';
    })
    .catch(() => { el.textContent = 'Verbindungsfehler'; el.style.color = 'var(--red, #ef4444)'; });
}
</script>
