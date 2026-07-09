<?php
// preview/templates/projects/bms/pages/portfolio.php
$seo   = $c['pages']['portfolio']['seo']      ?? [];
$secs  = $c['pages']['portfolio']['sections'] ?? [];

$hero_d    = $secs['hero']['data']        ?? [];
$proj_d    = $secs['projects']['data']    ?? [];
$proj_items = $proj_d['items']            ?? [];
$why_d     = $secs['why_custom']['data']  ?? [];
$why_items = $why_d['items']              ?? [];
$cta_d     = $secs['cta']['data']         ?? [];

echo bms_render_head(h($seo['title'] ?? 'Portfolio | BMS Digital Solutions'), $seo);
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

<!-- PROJECTS -->
<?php if (!empty($proj_items)): ?>
<section class="section-pad">
  <div class="container">
    <?php if (!empty($proj_d['headline'])): ?>
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($proj_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($proj_d['label']) ?></div><?php endif; ?>
      <h2><?= nl2br(h($proj_d['headline'])) ?></h2>
      <?php if (!empty($proj_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($proj_d['subtext']) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="bms-portfolio-grid">
      <?php foreach ($proj_items as $proj):
        $title = h($proj['title'] ?? '');
        if (!$title) continue;
        $cat  = h($proj['category'] ?? '');
        $year = h($proj['year']     ?? '');
        $desc = h($proj['description'] ?? '');
        $img  = h($proj['image']    ?? '');
        $url  = h($proj['url']      ?? '');
      ?>
      <div class="bms-portfolio-card">
        <?php if ($img): ?>
        <img src="<?= $img ?>" alt="<?= $title ?>" loading="lazy" class="bms-portfolio-img">
        <?php else: ?>
        <div class="bms-portfolio-img" style="display:flex;align-items:center;justify-content:center;font-size:3rem">🚀</div>
        <?php endif; ?>
        <div class="bms-portfolio-body">
          <?php if ($cat || $year): ?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
            <?php if ($cat): ?><span class="badge badge-accent" style="font-size:.7rem"><?= $cat ?></span><?php endif; ?>
            <?php if ($year): ?><span style="font-size:.75rem;color:var(--bms-muted);align-self:center"><?= $year ?></span><?php endif; ?>
          </div>
          <?php endif; ?>
          <h3 style="margin-bottom:8px;font-size:1.05rem"><?= $title ?></h3>
          <?php if ($desc): ?><p style="color:var(--bms-muted);font-size:.85rem;margin-bottom:14px;line-height:1.5"><?= $desc ?></p><?php endif; ?>
          <?php if ($url): ?><a href="<?= $url ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Ansehen →</a><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- WHY CUSTOM -->
<?php if (!empty($why_d['headline']) || !empty($why_items)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($why_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($why_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($why_d['headline'])): ?><h2><?= nl2br(h($why_d['headline'])) ?></h2><?php endif; ?>
      <?php if (!empty($why_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:560px;margin-inline:auto"><?= h($why_d['subtext']) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($why_items)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px">
      <?php foreach ($why_items as $item): ?>
      <div class="card">
        <div style="font-size:1.8rem;margin-bottom:12px"><?= h($item['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:8px"><?= h($item['title'] ?? '') ?></h3>
        <p style="color:var(--bms-muted);font-size:.875rem"><?= h($item['text'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
