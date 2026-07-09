<?php
// preview/templates/projects/bms/pages/news.php
$seo    = $c['pages']['news']['seo']      ?? [];
$secs   = $c['pages']['news']['sections'] ?? [];

$hero_d  = $secs['hero']['data']     ?? [];
$art_d   = $secs['articles']['data'] ?? [];
$art_i   = $art_d['items']           ?? [];
$cta_d   = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'News & Updates | BMS Digital Solutions'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:80px 0 48px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p><?= h($hero_d['subtext'] ?? '') ?></p>
  </div>
</section>

<!-- ARTICLES -->
<?php if (!empty($art_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div style="margin-bottom:40px">
      <?php if (!empty($art_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($art_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($art_d['headline'])): ?><h2><?= h($art_d['headline']) ?></h2><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
      <?php foreach ($art_i as $art): ?>
      <article class="card" style="display:flex;flex-direction:column">
        <?php if (!empty($art['image'])): ?>
        <img src="<?= h($art['image']) ?>" alt="<?= h($art['title'] ?? '') ?>" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:6px;margin-bottom:16px">
        <?php endif; ?>
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;flex-wrap:wrap">
          <?php if (!empty($art['category'])): ?><span style="font-size:.72rem;font-weight:600;color:var(--bms-accent);background:rgba(99,102,241,.12);padding:2px 8px;border-radius:4px"><?= h($art['category']) ?></span><?php endif; ?>
          <?php if (!empty($art['date'])): ?><span style="font-size:.72rem;color:var(--bms-muted)"><?= h($art['date']) ?></span><?php endif; ?>
        </div>
        <h3 style="font-size:1rem;margin-bottom:10px;line-height:1.4"><?= h($art['title'] ?? '') ?></h3>
        <?php if (!empty($art['summary'])): ?><p style="color:var(--bms-muted);font-size:.875rem;flex:1"><?= h($art['summary']) ?></p><?php endif; ?>
        <?php if (!empty($art['url'])): ?><a href="<?= h($art['url']) ?>" class="btn btn-outline btn-sm" style="margin-top:16px;align-self:flex-start">Weiterlesen →</a><?php endif; ?>
      </article>
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
