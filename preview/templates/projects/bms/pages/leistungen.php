<?php
// preview/templates/projects/bms/pages/leistungen.php
$seo   = $c['pages']['leistungen']['seo']      ?? [];
$secs  = $c['pages']['leistungen']['sections'] ?? [];

$hero_d  = $secs['hero']['data']      ?? [];
$svc_d   = $secs['services']['data']  ?? [];
$svc_items = $svc_d['items']          ?? [];
$cta_d   = $secs['cta']['data']       ?? [];

echo bms_render_head(h($seo['title'] ?? 'Leistungen | BMS Digital Solutions'), $seo);
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

<!-- SERVICES GRID -->
<?php if (!empty($svc_items)): ?>
<section class="section-pad">
  <div class="container">
    <?php if (!empty($svc_d['headline'])): ?>
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($svc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($svc_d['label']) ?></div><?php endif; ?>
      <h2><?= nl2br(h($svc_d['headline'])) ?></h2>
      <?php if (!empty($svc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($svc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
      <?php foreach ($svc_items as $svc):
        $title = h($svc['title'] ?? '');
        if (!$title) continue;
      ?>
      <div class="card" style="display:flex;flex-direction:column">
        <div style="font-size:2rem;margin-bottom:14px"><?= h($svc['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:6px"><?= $title ?></h3>
        <?php if (!empty($svc['short'])): ?><p style="color:var(--bms-accent);font-size:.82rem;font-weight:600;margin-bottom:10px"><?= h($svc['short']) ?></p><?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.875rem;flex:1;margin-bottom:16px"><?= h($svc['description'] ?? '') ?></p>
        <?php if (!empty($svc['price'])): ?>
        <div style="font-size:.82rem;color:var(--bms-muted);margin-bottom:12px">
          <span style="color:var(--bms-white);font-weight:700"><?= h($svc['price']) ?></span>
          <?= !empty($svc['price_unit']) ? ' · ' . h($svc['price_unit']) : '' ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($svc['url'])): ?><a href="<?= h($svc['url']) ?>" class="btn btn-outline btn-sm" style="align-self:flex-start">Details →</a><?php endif; ?>
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
