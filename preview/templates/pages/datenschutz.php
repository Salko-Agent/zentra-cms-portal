<?php
// preview/templates/pages/datenschutz.php

$seo = $c['pages']['datenschutz']['seo'] ?? [];
$d   = $c['pages']['datenschutz']['sections']['content']['data'] ?? [];

// sections_json may be stored as a JSON string or already decoded
$privacy_sections = $d['sections'] ?? [];
if (is_string($privacy_sections)) {
    $privacy_sections = json_decode($privacy_sections, true) ?: [];
}

$secs_html = '';
foreach ($privacy_sections as $sec) {
    $secs_html .= '<h2 style="font-size:1.1rem;margin:28px 0 10px">' . h($sec['title'] ?? '') . '</h2>'
        . '<p style="color:var(--text-secondary)">' . h($sec['text'] ?? '') . '</p>';
}

echo render_head(h($seo['title'] ?? 'Datenschutz | FlexFit Wien'), $seo);
echo render_navbar($c);
?>

<section class="page-hero" style="padding:80px 0 60px">
  <div class="container"><h1>Datenschutzerklärung</h1></div>
</section>

<section class="section-pad" style="background:var(--white)">
  <div class="container">
    <div style="max-width:800px">
      <p style="color:var(--text-secondary);margin-bottom:24px"><?= h($d['intro'] ?? '') ?></p>
      <?= $secs_html ?>
    </div>
  </div>
</section>

<?php echo render_footer($c); ?>
