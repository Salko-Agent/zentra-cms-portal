<?php
// preview/templates/projects/bms/pages/leistungen-software.php
$seo        = $c['pages']['leistungen-software']['seo']      ?? [];
$secs       = $c['pages']['leistungen-software']['sections'] ?? [];

$hero_d     = $secs['hero']['data']             ?? [];
$uc_d       = $secs['use_cases']['data']        ?? [];
$uc_i       = $uc_d['items']                    ?? [];
$feat_d     = $secs['featured']['data']         ?? [];
$feat_i     = $feat_d['items']                  ?? [];
$faq_d      = $secs['faq']['data']              ?? [];
$faq_i      = $faq_d['items']                   ?? [];
$cta_d      = $secs['cta']['data']              ?? [];

echo bms_render_head(h($seo['title'] ?? 'Software Entwicklung Wien | BMS'), $seo);
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

<!-- USE CASES -->
<?php if (!empty($uc_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($uc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($uc_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($uc_d['headline'])): ?><h2><?= h($uc_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($uc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($uc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
      <?php foreach ($uc_i as $uc): ?>
      <div class="card" style="display:flex;gap:16px;align-items:flex-start">
        <div style="font-size:1.6rem;flex-shrink:0"><?= h($uc['icon'] ?? '') ?></div>
        <div>
          <h3 style="margin-bottom:4px;font-size:1rem"><?= h($uc['title'] ?? '') ?></h3>
          <?php if (!empty($uc['short'])): ?><p style="color:var(--bms-accent);font-size:.8rem;font-weight:600;margin-bottom:6px"><?= h($uc['short']) ?></p><?php endif; ?>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($uc['description'] ?? '') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED PROJECTS -->
<?php if (!empty($feat_i)): ?>
<section class="section-pad">
  <div class="container">
    <div style="margin-bottom:40px">
      <?php if (!empty($feat_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($feat_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($feat_d['headline'])): ?><h2><?= h($feat_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($feat_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:10px"><?= h($feat_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
      <?php foreach ($feat_i as $proj): ?>
      <div class="bms-portfolio-card">
        <?php if (!empty($proj['image'])): ?>
        <img src="<?= h($proj['image']) ?>" alt="<?= h($proj['title'] ?? '') ?>" class="bms-portfolio-img">
        <?php else: ?>
        <div class="bms-portfolio-img" style="display:flex;align-items:center;justify-content:center;font-size:2rem">⚙️</div>
        <?php endif; ?>
        <div class="bms-portfolio-body">
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
            <?php if (!empty($proj['category'])): ?><span style="font-size:.72rem;font-weight:600;color:var(--bms-accent);background:rgba(99,102,241,.12);padding:2px 8px;border-radius:4px"><?= h($proj['category']) ?></span><?php endif; ?>
            <?php if (!empty($proj['year'])): ?><span style="font-size:.72rem;color:var(--bms-muted)"><?= h($proj['year']) ?></span><?php endif; ?>
          </div>
          <h3 style="font-size:1rem;margin-bottom:6px"><?= h($proj['title'] ?? '') ?></h3>
          <p style="color:var(--bms-muted);font-size:.82rem"><?= h($proj['description'] ?? '') ?></p>
          <?php if (!empty($proj['url'])): ?><a href="<?= h($proj['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:12px;align-self:flex-start">Ansehen →</a><?php endif; ?>
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
