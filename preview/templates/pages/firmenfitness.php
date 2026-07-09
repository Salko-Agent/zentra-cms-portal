<?php
// preview/templates/pages/firmenfitness.php

$seo   = $c['pages']['firmenfitness']['seo'] ?? [];
$secs  = $c['pages']['firmenfitness']['sections'] ?? [];
$hero  = $secs['hero']['data']  ?? [];
$intro = $secs['intro']['data'] ?? [];
$cta   = $secs['cta']['data']   ?? [];

echo render_head(h($seo['title'] ?? 'Firmenfitness Wien | FlexFit'), $seo);
echo render_navbar($c);
?>

<section class="page-hero<?= !empty($hero['bg_image']) ? ' page-hero--image' : '' ?>">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= h($hero['label'] ?? '') ?></span>
    <h1><?= nl2br(h($hero['headline'] ?? '')) ?></h1>
    <p><?= h($hero['subtext'] ?? '') ?></p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:32px">
      <a href="<?= h($hero['cta_primary_url'] ?? '/kontakt') ?>" class="btn btn-primary btn-lg"><?= h($hero['cta_primary'] ?? 'Jetzt anfragen') ?> &rarr;</a>
    </div>
  </div>
</section>

<section class="section-pad" style="background:var(--white)">
  <div class="container" style="max-width:820px">
    <div class="text-center reveal">
      <span class="section-label"><?= h($intro['label'] ?? '') ?></span>
      <h2 style="margin-bottom:24px"><?= nl2br(h($intro['headline'] ?? '')) ?></h2>
      <p style="color:var(--text-secondary);font-size:1.05rem;line-height:1.7"><?= h($intro['text1'] ?? '') ?></p>
    </div>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= h($cta['headline'] ?? '') ?></h2>
    <p><?= h($cta['subtext'] ?? '') ?></p>
    <div class="cta-banner-actions">
      <a href="<?= h($cta['cta_primary_url'] ?? '/kontakt') ?>" class="btn btn-primary btn-lg"><?= h($cta['cta_primary'] ?? 'Jetzt anfragen') ?> &rarr;</a>
      <?php if (!empty($cta['cta_secondary'])): ?>
      <a href="<?= h($cta['cta_secondary_url'] ?? '#') ?>" class="btn btn-outline btn-lg"><?= h($cta['cta_secondary']) ?></a>
      <?php endif; ?>
    </div>
  </div></div>
</section>

<?php echo render_footer($c); ?>
