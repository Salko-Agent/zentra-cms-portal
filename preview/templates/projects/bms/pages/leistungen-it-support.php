<?php
// preview/templates/projects/bms/pages/leistungen-it-support.php
$seo     = $c['pages']['leistungen-it-support']['seo']      ?? [];
$secs    = $c['pages']['leistungen-it-support']['sections'] ?? [];

$hero_d  = $secs['hero']['data']     ?? [];
$svc_d   = $secs['services']['data'] ?? [];
$svc_i   = $svc_d['items']           ?? [];
$faq_d   = $secs['faq']['data']      ?? [];
$faq_i   = $faq_d['items']           ?? [];
$cta_d   = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'IT-Support Wien | BMS Digital Solutions'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p><?= h($hero_d['subtext'] ?? '') ?></p>
    <div class="bms-hero-actions" style="margin-top:28px">
      <?php if (!empty($hero_d['cta_primary'])): ?><a href="<?= h($hero_d['cta_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($hero_d['cta_primary']) ?></a><?php endif; ?>
      <?php if (!empty($hero_d['cta_secondary'])): ?><a href="<?= h($hero_d['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($hero_d['cta_secondary']) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<!-- SERVICES -->
<?php if (!empty($svc_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($svc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($svc_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($svc_d['headline'])): ?><h2><?= h($svc_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($svc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:580px;margin-inline:auto"><?= h($svc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
      <?php foreach ($svc_i as $svc): ?>
      <div class="card" style="display:flex;flex-direction:column">
        <div style="font-size:2rem;margin-bottom:12px"><?= h($svc['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:4px"><?= h($svc['title'] ?? '') ?></h3>
        <?php if (!empty($svc['short'])): ?><p style="color:var(--bms-accent);font-size:.9rem;font-weight:700;margin-bottom:8px"><?= h($svc['short']) ?></p><?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.875rem;flex:1"><?= h($svc['description'] ?? '') ?></p>
        <?php if (!empty($svc['url'])): ?><a href="<?= h($svc['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:16px;align-self:flex-start">Termin buchen →</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:32px;padding:20px 24px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:var(--bms-r);text-align:center">
      <p style="color:var(--bms-muted);font-size:.9rem">Alle Services werden <strong style="color:var(--bms-text)">fair nach Aufwand abgerechnet</strong> – keine Pauschalen, keine Mindestzeiten, keine Vertragsbindung.</p>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php if (!empty($faq_i)): ?>
<section class="section-pad">
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
<section class="section-pad" style="background:var(--bms-dark)">
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
