<?php
// preview/templates/projects/bms/pages/preise.php
$seo    = $c['pages']['preise']['seo']      ?? [];
$secs   = $c['pages']['preise']['sections'] ?? [];

$hero_d = $secs['hero']['data']             ?? [];
$sp_d   = $secs['starting_prices']['data']  ?? [];
$sp_i   = $sp_d['items']                    ?? [];
$proc_d = $secs['process']['data']          ?? [];
$proc_i = $proc_d['items']                  ?? [];
$cta_d  = $secs['cta']['data']              ?? [];

echo bms_render_head(h($seo['title'] ?? 'Preise | BMS Digital Solutions Wien'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p style="max-width:640px;margin-inline:auto"><?= h($hero_d['subtext'] ?? '') ?></p>
    <div class="bms-hero-actions" style="margin-top:28px">
      <?php if (!empty($hero_d['cta_primary'])): ?><a href="<?= h($hero_d['cta_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($hero_d['cta_primary']) ?></a><?php endif; ?>
      <?php if (!empty($hero_d['cta_secondary'])): ?><a href="<?= h($hero_d['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($hero_d['cta_secondary']) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<!-- STARTING PRICES -->
<?php if (!empty($sp_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($sp_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($sp_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($sp_d['headline'])): ?><h2><?= h($sp_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($sp_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($sp_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;max-width:900px;margin-inline:auto">
      <?php foreach ($sp_i as $price): ?>
      <div class="card" style="text-align:center;display:flex;flex-direction:column;align-items:center">
        <div style="font-size:2rem;margin-bottom:12px"><?= h($price['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:8px"><?= h($price['title'] ?? '') ?></h3>
        <?php if (!empty($price['short'])): ?>
        <div style="font-size:1.5rem;font-weight:800;font-family:var(--font-head);color:var(--bms-accent);margin-bottom:10px"><?= h($price['short']) ?></div>
        <?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.875rem;flex:1"><?= h($price['description'] ?? '') ?></p>
        <?php if (!empty($price['url'])): ?><a href="<?= h($price['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:16px">Details →</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- PROCESS -->
<?php if (!empty($proc_i)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($proc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($proc_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($proc_d['headline'])): ?><h2><?= h($proc_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($proc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($proc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="max-width:720px;margin-inline:auto;display:flex;flex-direction:column;gap:0">
      <?php foreach ($proc_i as $i => $step): ?>
      <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:<?= $i < count($proc_i)-1 ? '0' : '0' ?>">
        <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
          <div style="width:44px;height:44px;border-radius:50%;background:rgba(99,102,241,.15);border:2px solid var(--bms-accent);display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:700;color:var(--bms-accent)"><?= h($step['number'] ?? '') ?></div>
          <?php if ($i < count($proc_i)-1): ?><div style="width:2px;height:32px;background:var(--bms-border);margin:4px 0"></div><?php endif; ?>
        </div>
        <div style="padding-bottom:<?= $i < count($proc_i)-1 ? '24px' : '0' ?>">
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
