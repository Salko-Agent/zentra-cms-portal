<?php
// preview/templates/projects/bms/pages/leistungen-websites.php
$seo   = $c['pages']['leistungen-websites']['seo']      ?? [];
$secs  = $c['pages']['leistungen-websites']['sections'] ?? [];

$hero_d     = $secs['hero']['data']       ?? [];
$str_d      = $secs['strengths']['data']  ?? [];
$str_items  = $str_d['items']             ?? [];
$faq_d      = $secs['faq']['data']        ?? [];
$faq_items  = $faq_d['items']             ?? [];
$cta_d      = $secs['cta']['data']        ?? [];

echo bms_render_head(h($seo['title'] ?? 'Webentwicklung Wien | BMS Digital Solutions'), $seo);
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

<!-- STRENGTHS -->
<?php if (!empty($str_items)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($str_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($str_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($str_d['headline'])): ?><h2><?= nl2br(h($str_d['headline'])) ?></h2><?php endif; ?>
      <?php if (!empty($str_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($str_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
      <?php foreach ($str_items as $item): ?>
      <div class="card" style="display:flex;gap:16px;align-items:flex-start">
        <div style="font-size:1.6rem;flex-shrink:0"><?= h($item['icon'] ?? '') ?></div>
        <div>
          <h3 style="margin-bottom:6px;font-size:1rem"><?= h($item['title'] ?? '') ?></h3>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($item['text'] ?? '') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php if (!empty($faq_items)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:40px">
      <?php if (!empty($faq_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($faq_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($faq_d['headline'])): ?><h2><?= nl2br(h($faq_d['headline'])) ?></h2><?php endif; ?>
    </div>
    <div style="max-width:720px;margin-inline:auto">
      <?php foreach ($faq_items as $i => $faq):
        $q = h($faq['question'] ?? '');
        $a = h($faq['answer']   ?? '');
        if (!$q) continue;
      ?>
      <div class="bms-faq-item">
        <button class="bms-faq-btn"
          onclick="var b=this.nextElementSibling;var open=b.style.display==='block';document.querySelectorAll('.bms-faq-body').forEach(function(x){x.style.display='none';x.previousElementSibling.querySelector('.faq-icon').textContent='+'});if(!open){b.style.display='block';this.querySelector('.faq-icon').textContent='−'}"
          aria-expanded="false">
          <?= $q ?>
          <span class="faq-icon" style="font-size:1.3rem;color:var(--bms-accent);flex-shrink:0">+</span>
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
