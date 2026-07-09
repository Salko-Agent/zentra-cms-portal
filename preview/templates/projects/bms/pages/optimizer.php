<?php
// preview/templates/projects/bms/pages/optimizer.php
$seo    = $c['pages']['optimizer']['seo']      ?? [];
$secs   = $c['pages']['optimizer']['sections'] ?? [];

$hero_d = $secs['hero']['data']     ?? [];
$feat_d = $secs['features']['data'] ?? [];
$feat_i = $feat_d['items']          ?? [];
$cta_d  = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'BMS Optimizer | Kostenloser Windows Performance Tuner'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p style="max-width:560px;margin-inline:auto"><?= h($hero_d['subtext'] ?? '') ?></p>
    <div style="margin-top:10px;font-size:.82rem;color:var(--bms-muted)">Windows 10/11 · Kostenlos · Kein Abo · KI-gestützt</div>
    <div class="bms-hero-actions" style="margin-top:28px">
      <?php if (!empty($hero_d['cta_primary'])): ?><a href="<?= h($hero_d['cta_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($hero_d['cta_primary']) ?></a><?php endif; ?>
      <?php if (!empty($hero_d['cta_secondary'])): ?><a href="<?= h($hero_d['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($hero_d['cta_secondary']) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<!-- FEATURES -->
<?php if (!empty($feat_i)): ?>
<section class="section-pad" id="features" style="background:var(--bms-dark)">
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
          <h3 style="font-size:1rem;margin-bottom:4px"><?= h($feat['title'] ?? '') ?></h3>
          <?php if (!empty($feat['short'])): ?><p style="color:var(--bms-accent);font-size:.8rem;font-weight:600;margin-bottom:6px"><?= h($feat['short']) ?></p><?php endif; ?>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($feat['description'] ?? $feat['text'] ?? '') ?></p>
        </div>
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
