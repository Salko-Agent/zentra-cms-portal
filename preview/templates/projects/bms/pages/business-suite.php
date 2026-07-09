<?php
// preview/templates/projects/bms/pages/business-suite.php
$seo     = $c['pages']['business-suite']['seo']      ?? [];
$secs    = $c['pages']['business-suite']['sections'] ?? [];

$hero_d  = $secs['hero']['data']     ?? [];
$feat_d  = $secs['features']['data'] ?? [];
$feat_i  = $feat_d['items']          ?? [];
$faq_d   = $secs['faq']['data']      ?? [];
$faq_i   = $faq_d['items']           ?? [];
$cta_d   = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'BMS Business Suite | Offline Software'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p style="max-width:580px;margin-inline:auto"><?= h($hero_d['subtext'] ?? '') ?></p>
    <div style="margin-top:12px;font-size:.82rem;color:var(--bms-muted)">Windows 10/11 · Offline · Einmalzahlung · Lifetime Updates</div>
    <div class="bms-hero-actions" style="margin-top:28px">
      <?php if (!empty($hero_d['cta_primary'])): ?><a href="<?= h($hero_d['cta_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($hero_d['cta_primary']) ?></a><?php endif; ?>
      <?php if (!empty($hero_d['cta_secondary'])): ?><a href="<?= h($hero_d['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($hero_d['cta_secondary']) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<!-- VS BANNER -->
<div style="background:rgba(99,102,241,.06);border-top:1px solid var(--bms-border);border-bottom:1px solid var(--bms-border);padding:20px 24px;text-align:center">
  <div class="container">
    <p style="color:var(--bms-muted);font-size:.875rem">Kein Lexoffice, kein Sevdesk, kein Monatsabo – <strong style="color:var(--bms-text)">€59 einmalig</strong>, alle Daten bleiben auf Ihrem Gerät.</p>
  </div>
</div>

<!-- FEATURES -->
<?php if (!empty($feat_i)): ?>
<section class="section-pad" id="features">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($feat_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($feat_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($feat_d['headline'])): ?><h2><?= h($feat_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($feat_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($feat_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
      <?php foreach ($feat_i as $feat): ?>
      <div class="card" style="display:flex;gap:16px;align-items:flex-start">
        <div style="font-size:1.8rem;flex-shrink:0"><?= h($feat['icon'] ?? '') ?></div>
        <div>
          <h3 style="margin-bottom:4px;font-size:1rem"><?= h($feat['title'] ?? '') ?></h3>
          <?php if (!empty($feat['short'])): ?><p style="color:var(--bms-accent);font-size:.8rem;font-weight:600;margin-bottom:6px"><?= h($feat['short']) ?></p><?php endif; ?>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($feat['description'] ?? '') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php if (!empty($faq_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:40px">
      <?php if (!empty($faq_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($faq_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($faq_d['headline'])): ?><h2><?= h($faq_d['headline']) ?></h2><?php endif; ?>
    </div>
    <div style="max-width:720px;margin-inline:auto">
      <?php foreach ($faq_i as $faq):
        $q = h($faq['question'] ?? ''); $a = h($faq['answer'] ?? '');
        if (!$q) continue; ?>
      <div class="bms-faq-item">
        <button class="bms-faq-btn"
          onclick="var b=this.nextElementSibling;var open=b.style.display==='block';document.querySelectorAll('.bms-faq-body').forEach(function(x){x.style.display='none';x.previousElementSibling.querySelector('.faq-icon').textContent='+'});if(!open){b.style.display='block';this.querySelector('.faq-icon').textContent='−'}"
          aria-expanded="false">
          <?= $q ?><span class="faq-icon" style="font-size:1.3rem;color:var(--bms-accent);flex-shrink:0">+</span>
        </button>
        <div class="bms-faq-body"><?= nl2br($a) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<?php if (!empty($cta_d['headline'])): ?>
<section class="section-pad">
  <div class="container">
    <div class="bms-cta">
      <h2><?= h($cta_d['headline']) ?></h2>
      <?php if (!empty($cta_d['subtext'])): ?><p><?= h($cta_d['subtext']) ?></p><?php endif; ?>
      <div class="bms-cta-actions">
        <?php if (!empty($cta_d['btn_primary'])): ?><a href="<?= h($cta_d['btn_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($cta_d['btn_primary']) ?></a><?php endif; ?>
        <?php if (!empty($cta_d['btn_secondary'])): ?><a href="<?= h($cta_d['btn_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($cta_d['btn_secondary']) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo bms_render_footer($c); ?>
