<?php
// preview/templates/projects/bms/pages/kontakt.php
$seo   = $c['pages']['kontakt']['seo']      ?? [];
$secs  = $c['pages']['kontakt']['sections'] ?? [];

$hero_d  = $secs['hero']['data']         ?? [];
$info_d  = $secs['contact']['data']      ?? [];
$steps_d = $secs['expectations']['data'] ?? [];
$steps   = $steps_d['items']             ?? [];

echo bms_render_head(h($seo['title'] ?? 'Kontakt | BMS Digital Solutions'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p><?= h($hero_d['subtext'] ?? '') ?></p>
  </div>
</section>

<!-- CONTACT INFO + FORM -->
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start">
      <!-- Contact Methods -->
      <div>
        <h2 style="margin-bottom:28px"><?= h($info_d['headline'] ?? 'Direkter Kontakt') ?></h2>
        <?php if (!empty($info_d['subtext'])): ?><p style="color:var(--bms-muted);margin-bottom:32px"><?= h($info_d['subtext']) ?></p><?php endif; ?>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php if (!empty($info_d['email'])): ?>
          <div class="card" style="display:flex;gap:16px;align-items:center;padding:20px">
            <div style="font-size:1.5rem">📧</div>
            <div>
              <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--bms-muted);margin-bottom:2px">E-Mail</div>
              <a href="mailto:<?= h($info_d['email']) ?>" style="color:var(--bms-white);font-weight:600"><?= h($info_d['email']) ?></a>
            </div>
          </div>
          <?php endif; ?>
          <?php if (!empty($info_d['phone'])): ?>
          <div class="card" style="display:flex;gap:16px;align-items:center;padding:20px">
            <div style="font-size:1.5rem">📞</div>
            <div>
              <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--bms-muted);margin-bottom:2px">Telefon</div>
              <a href="tel:<?= h($info_d['phone']) ?>" style="color:var(--bms-white);font-weight:600"><?= h($info_d['phone']) ?></a>
              <?php if (!empty($info_d['hours'])): ?><div style="font-size:.8rem;color:var(--bms-muted);margin-top:2px"><?= h($info_d['hours']) ?></div><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if (!empty($info_d['address'])): ?>
          <div class="card" style="display:flex;gap:16px;align-items:center;padding:20px">
            <div style="font-size:1.5rem">📍</div>
            <div>
              <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--bms-muted);margin-bottom:2px">Standort</div>
              <div style="color:var(--bms-white);font-weight:600"><?= h($info_d['address']) ?></div>
            </div>
          </div>
          <?php endif; ?>
          <a href="https://calendly.com/bmstrio99/erstberatung-30" target="_blank" rel="noopener"
             class="btn btn-primary" style="justify-content:center">📅 Erstgespräch buchen (kostenlos)</a>
        </div>
      </div>
      <!-- Contact Form -->
      <div>
        <div class="card">
          <h3 style="margin-bottom:4px">Direktnachricht</h3>
          <p style="color:var(--bms-muted);font-size:.875rem;margin-bottom:24px">Antwort innerhalb von 24 Stunden.</p>
          <form>
            <div style="margin-bottom:16px">
              <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px;color:var(--bms-muted)">Name *</label>
              <input type="text" placeholder="Ihr Name" style="width:100%;background:rgba(255,255,255,.05);border:1px solid var(--bms-border);border-radius:8px;padding:10px 14px;color:var(--bms-white);font-family:inherit;font-size:.9rem">
            </div>
            <div style="margin-bottom:16px">
              <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px;color:var(--bms-muted)">E-Mail *</label>
              <input type="email" placeholder="ihre@email.com" style="width:100%;background:rgba(255,255,255,.05);border:1px solid var(--bms-border);border-radius:8px;padding:10px 14px;color:var(--bms-white);font-family:inherit;font-size:.9rem">
            </div>
            <div style="margin-bottom:16px">
              <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:6px;color:var(--bms-muted)">Nachricht *</label>
              <textarea rows="4" placeholder="Ihr Vorhaben..." style="width:100%;background:rgba(255,255,255,.05);border:1px solid var(--bms-border);border-radius:8px;padding:10px 14px;color:var(--bms-white);font-family:inherit;font-size:.9rem;resize:vertical"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Nachricht senden →</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STEPS -->
<?php if (!empty($steps)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:40px">
      <?php if (!empty($steps_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($steps_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($steps_d['headline'])): ?><h2><?= nl2br(h($steps_d['headline'])) ?></h2><?php endif; ?>
      <?php if (!empty($steps_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($steps_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div class="bms-timeline" style="max-width:600px;margin-inline:auto">
      <?php foreach ($steps as $step): ?>
      <div class="bms-timeline-item">
        <div class="bms-timeline-dot" style="font-size:.9rem;font-weight:800;color:var(--bms-accent)"><?= h($step['number'] ?? '') ?></div>
        <div class="bms-timeline-body">
          <h3 style="margin-bottom:4px;font-size:1rem"><?= h($step['title'] ?? '') ?></h3>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($step['text'] ?? '') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo bms_render_footer($c); ?>
