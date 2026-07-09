<?php
// preview/templates/projects/bms/pages/software.php
$seo     = $c['pages']['software']['seo']      ?? [];
$secs    = $c['pages']['software']['sections'] ?? [];

$hero_d  = $secs['hero']['data']     ?? [];
$prod_d  = $secs['products']['data'] ?? [];
$prod_i  = $prod_d['items']          ?? [];
$cta_d   = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'Software & Produkte | BMS Digital Solutions'), $seo);
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

<!-- PRODUCTS -->
<?php if (!empty($prod_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($prod_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($prod_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($prod_d['headline'])): ?><h2><?= h($prod_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($prod_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($prod_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;max-width:800px;margin-inline:auto">
      <?php foreach ($prod_i as $prod): ?>
      <div class="card" style="display:flex;flex-direction:column;padding:36px">
        <div style="font-size:2.8rem;margin-bottom:16px"><?= h($prod['icon'] ?? '') ?></div>
        <h3 style="font-size:1.3rem;margin-bottom:6px"><?= h($prod['title'] ?? '') ?></h3>
        <?php if (!empty($prod['short'])): ?>
        <div style="display:inline-block;padding:4px 12px;background:rgba(99,102,241,.15);color:var(--bms-accent);border-radius:6px;font-size:.82rem;font-weight:700;margin-bottom:14px;width:fit-content"><?= h($prod['short']) ?></div>
        <?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.9rem;flex:1;line-height:1.7"><?= h($prod['description'] ?? '') ?></p>
        <?php if (!empty($prod['url'])): ?><a href="<?= h($prod['url']) ?>" class="btn btn-primary" style="margin-top:20px;align-self:flex-start">Mehr erfahren →</a><?php endif; ?>
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
