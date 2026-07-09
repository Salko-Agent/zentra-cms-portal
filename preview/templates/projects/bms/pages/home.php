<?php
// preview/templates/projects/bms/pages/home.php
$seo  = $c['pages']['home']['seo']  ?? [];
$secs = $c['pages']['home']['sections'] ?? [];

$hero_d = $secs['hero']['data']     ?? [];
$stats  = ($secs['stats']['data']['items'] ?? []);
$why_d  = $secs['why']['data']      ?? [];
$why_items = $why_d['items']        ?? [];
$svc_d  = $secs['services']['data'] ?? [];
$svc_items = $svc_d['items']        ?? [];
$cta_d  = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'BMS Digital Solutions Wien'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero section-pad" style="padding-top:120px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?>
    <div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div>
    <?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? 'Webentwicklung Wien')) ?></h1>
    <p><?= h($hero_d['subtext'] ?? '') ?></p>
    <div class="bms-hero-actions">
      <?php if (!empty($hero_d['cta_primary'])): ?>
      <a href="<?= h($hero_d['cta_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($hero_d['cta_primary']) ?></a>
      <?php endif; ?>
      <?php if (!empty($hero_d['cta_secondary'])): ?>
      <a href="<?= h($hero_d['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($hero_d['cta_secondary']) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- STATS -->
<?php if (!empty($stats)): ?>
<section style="background:var(--bms-dark);padding:0">
  <div class="container">
    <div class="bms-stats" style="border-top:1px solid var(--bms-border);border-bottom:1px solid var(--bms-border)">
      <?php foreach ($stats as $stat): ?>
      <div class="bms-stat">
        <div class="bms-stat-number"><?= h($stat['number'] ?? '') ?></div>
        <div class="bms-stat-label"><?= h($stat['label'] ?? '') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- WHY BMS -->
<?php if (!empty($why_d['headline']) || !empty($why_items)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div style="max-width:560px;margin-bottom:48px">
      <?php if (!empty($why_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($why_d['label']) ?></div><?php endif; ?>
      <h2><?= nl2br(h($why_d['headline'] ?? '')) ?></h2>
      <?php if (!empty($why_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px"><?= h($why_d['subtext']) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($why_items)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
      <?php foreach ($why_items as $item): ?>
      <div class="card">
        <div style="font-size:2rem;margin-bottom:12px"><?= h($item['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:8px"><?= h($item['title'] ?? '') ?></h3>
        <p style="color:var(--bms-muted);font-size:.9rem"><?= h($item['text'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($why_d['text'])): ?>
    <div style="margin-top:32px;padding:24px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:var(--bms-r)">
      <p style="color:var(--bms-text)"><?= h($why_d['text']) ?></p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- SERVICES -->
<?php if (!empty($svc_items)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($svc_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($svc_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($svc_d['headline'])): ?><h2><?= nl2br(h($svc_d['headline'])) ?></h2><?php endif; ?>
      <?php if (!empty($svc_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($svc_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
      <?php foreach ($svc_items as $svc): ?>
      <div class="card" style="display:flex;flex-direction:column">
        <div style="font-size:2rem;margin-bottom:12px"><?= h($svc['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:4px"><?= h($svc['title'] ?? '') ?></h3>
        <?php if (!empty($svc['short'])): ?><p style="color:var(--bms-accent);font-size:.82rem;font-weight:600;margin-bottom:8px"><?= h($svc['short']) ?></p><?php endif; ?>
        <p style="color:var(--bms-muted);font-size:.875rem;flex:1"><?= h($svc['description'] ?? '') ?></p>
        <?php if (!empty($svc['url'])): ?><a href="<?= h($svc['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:16px;align-self:flex-start">Details →</a><?php endif; ?>
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
        <?php if (!empty($cta_d['btn_primary'])): ?>
        <a href="<?= h($cta_d['btn_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($cta_d['btn_primary']) ?></a>
        <?php endif; ?>
        <?php if (!empty($cta_d['btn_secondary'])): ?>
        <a href="<?= h($cta_d['btn_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($cta_d['btn_secondary']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo bms_render_footer($c); ?>
