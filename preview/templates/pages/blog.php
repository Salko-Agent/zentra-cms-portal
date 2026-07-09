<?php
// preview/templates/pages/blog.php

$seo   = $c['pages']['blog']['seo'] ?? [];
$secs  = $c['pages']['blog']['sections'] ?? [];
$hero  = $secs['hero']['data']     ?? [];
$arts  = $secs['articles']['data'] ?? [];
$cta   = $secs['cta']['data']      ?? [];

echo render_head(h($seo['title'] ?? 'Blog & Insights | FlexFit Wien'), $seo);
echo render_navbar($c);

$article_html = '';
foreach ($arts['items'] ?? [] as $a) {
    $article_html .= '<a href="' . h($a['url'] ?? '#') . '" target="_blank" rel="noopener" class="blog-card">'
        . '<div class="blog-card-emoji">' . h($a['emoji'] ?? '📄') . '</div>'
        . '<div class="blog-card-body">'
        . '<div class="blog-card-date">' . h($a['date'] ?? '') . '</div>'
        . '<h3 class="blog-card-title">' . h($a['title'] ?? '') . '</h3>'
        . '<p class="blog-card-excerpt">' . h($a['excerpt'] ?? '') . '</p>'
        . '<span class="blog-card-link">Weiterlesen →</span>'
        . '</div></a>';
}
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= h($hero['label'] ?? 'Blog & Wissen') ?></span>
    <h1><?= nl2br(h($hero['headline'] ?? '')) ?></h1>
    <p><?= h($hero['subtext'] ?? '') ?></p>
  </div>
</section>

<section class="blog-section section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="blog-grid"><?= $article_html ?></div>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= h($cta['headline'] ?? '') ?></h2>
    <p><?= h($cta['subtext'] ?? '') ?></p>
    <div class="cta-banner-actions">
      <a href="/probetraining" class="btn btn-primary btn-lg"><?= h($cta['btn_primary'] ?? 'Probetraining buchen →') ?></a>
    </div>
  </div></div>
</section>

<?php echo render_footer($c); ?>
