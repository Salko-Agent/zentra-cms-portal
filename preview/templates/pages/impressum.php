<?php
// preview/templates/pages/impressum.php

$seo = $c['pages']['impressum']['seo'] ?? [];
$d   = $c['pages']['impressum']['sections']['content']['data'] ?? [];

echo render_head(h($seo['title'] ?? 'Impressum | FlexFit Wien'), $seo);
echo render_navbar($c);
?>

<section class="page-hero" style="padding:80px 0 60px">
  <div class="container"><h1>Impressum</h1></div>
</section>

<section class="section-pad" style="background:var(--white)">
  <div class="container">
    <div style="max-width:800px">
      <h2 style="font-size:1.3rem;margin-bottom:24px">Inhalte, Medieninhaber, Herausgeber</h2>
      <div style="background:var(--off-white);border-radius:var(--r-md);padding:28px;border:1px solid var(--border-light);margin-bottom:32px">
        <p style="font-weight:700;margin-bottom:4px"><?= h($d['person'] ?? '') ?></p>
        <p style="color:var(--text-secondary);margin-bottom:16px"><?= h($d['person_role'] ?? '') ?></p>
        <p style="font-weight:700;margin-bottom:8px"><?= h($d['company'] ?? '') ?></p>
        <p style="color:var(--text-secondary)"><?= nl2br(h($d['address'] ?? '')) ?><br>Tel.: <?= h($d['phone'] ?? '') ?><br>Mail: <a href="mailto:<?= h($d['email'] ?? '') ?>" style="color:var(--gold)"><?= h($d['email'] ?? '') ?></a></p>
      </div>
      <h2 style="font-size:1.3rem;margin-bottom:16px">Nutzungsbedingungen, Gewährleistung, Links</h2>
      <p style="color:var(--text-secondary);margin-bottom:32px"><?= h($d['nutzungsbedingungen'] ?? '') ?></p>
      <h2 style="font-size:1.3rem;margin-bottom:16px">Webanalyse</h2>
      <p style="color:var(--text-secondary)"><?= h($d['analytics_text'] ?? '') ?></p>
    </div>
  </div>
</section>

<?php echo render_footer($c); ?>
