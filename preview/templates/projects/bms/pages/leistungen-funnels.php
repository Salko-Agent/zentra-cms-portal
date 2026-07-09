<?php
// preview/templates/projects/bms/pages/leistungen-funnels.php
$seo        = $c['pages']['leistungen-funnels']['seo']      ?? [];
$secs       = $c['pages']['leistungen-funnels']['sections'] ?? [];

$hero_d     = $secs['hero']['data']           ?? [];
$types_d    = $secs['funnel_types']['data']   ?? [];
$types_i    = $types_d['items']               ?? [];
$proc_d     = $secs['process']['data']        ?? [];
$proc_i     = $proc_d['items']                ?? [];
$cta_d      = $secs['cta']['data']            ?? [];

echo bms_render_head(h($seo['title'] ?? 'Sales Funnel Entwicklung Wien | BMS'), $seo);
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

<!-- FUNNEL TYPES -->
<?php if (!empty($types_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($types_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($types_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($types_d['headline'])): ?><h2><?= h($types_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($types_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($types_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
      <?php foreach ($types_i as $t): ?>
      <div class="card" style="display:flex;flex-direction:column">
        <div style="font-size:2.2rem;margin-bottom:12px"><?= h($t['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:4px"><?= h($t['title'] ?? '') ?></h3>
        <?php if (!empty($t['short'])): ?><p style="color:var(--bms-accent);font-size:.9rem;font-weight:700;margin-bottom:10px"><?= h($t['short']) ?></p><?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.875rem;flex:1"><?= h($t['description'] ?? '') ?></p>
        <?php if (!empty($t['url'])): ?><a href="<?= h($t['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:16px;align-self:flex-start">Preise ansehen →</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- PROCESS STEPS -->
<?php if (!empty($proc_i)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($proc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($proc_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($proc_d['headline'])): ?><h2><?= h($proc_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($proc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($proc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="max-width:720px;margin-inline:auto;display:flex;flex-direction:column;gap:16px">
      <?php foreach ($proc_i as $step): ?>
      <div class="card" style="display:flex;gap:20px;align-items:flex-start">
        <div style="flex-shrink:0;width:44px;height:44px;border-radius:50%;background:rgba(99,102,241,.15);border:1px solid var(--bms-accent);display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;color:var(--bms-accent)"><?= h($step['number'] ?? '') ?></div>
        <div>
          <h3 style="margin-bottom:6px;font-size:1rem"><?= h($step['title'] ?? '') ?></h3>
          <p style="color:var(--bms-muted);font-size:.875rem"><?= h($step['text'] ?? '') ?></p>
        </div>
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
