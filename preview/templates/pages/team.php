<?php
// preview/templates/pages/team.php

$seo      = $c['pages']['team']['seo'] ?? [];
$secs     = $c['pages']['team']['sections'] ?? [];
$hero     = $secs['hero']['data']     ?? [];
$trainers = $secs['trainers']['data'] ?? [];
$physio   = $secs['physio']['data']   ?? [];
$cta      = $secs['cta']['data']      ?? [];

echo render_head(h($seo['title'] ?? 'Unser Team | FlexFit Wien 1080'), $seo);
echo render_navbar($c);

$trainer_html = '';
foreach ($trainers['items'] ?? [] as $i => $t) {
    $name  = h($t['name']  ?? '');
    $title = h($t['title'] ?? '');
    $creds = h($t['credentials'] ?? '');
    $bio   = h($t['bio']   ?? '');
    $photo = h($t['photo'] ?? '');

    $specs_html = '';
    $specs_raw = $t['specializations'] ?? [];
    if (is_string($specs_raw)) {
        $dec = json_decode($specs_raw, true);
        $specs_raw = is_array($dec) ? $dec : array_values(array_filter(array_map('trim', explode("\n", $specs_raw))));
    }
    foreach ($specs_raw as $sp) {
        $specs_html .= '<li style="font-size:.83rem;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.07);color:rgba(255,255,255,.7)">' . h($sp) . '</li>';
    }

    $photo_tag = $photo
        ? '<img src="' . $photo . '" alt="' . $name . '" loading="lazy" style="width:100%;height:280px;object-fit:cover;object-position:top">'
        : '<div style="background:var(--gold);height:280px;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:3rem;font-weight:900;color:var(--black)">' . h(mb_substr($t['name'] ?? '?', 0, 2)) . '</div>';

    $delay = $i + 1;
    $trainer_html .= '<div class="card reveal reveal-delay-' . $delay . '">'
        . '<div style="overflow:hidden;border-radius:var(--r-lg) var(--r-lg) 0 0">' . $photo_tag . '</div>'
        . '<div class="card-body" style="padding:24px;background:var(--dark);flex:1;display:flex;flex-direction:column">'
        . '<div style="font-weight:700;font-size:1.1rem;color:var(--white);margin-bottom:4px">' . $name . '</div>'
        . '<div style="font-size:.82rem;color:var(--gold);margin-bottom:8px">' . $title . '</div>'
        . '<div style="font-size:.8rem;color:rgba(255,255,255,.5);margin-bottom:16px">' . $creds . '</div>'
        . '<p style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:16px;line-height:1.6">' . $bio . '</p>'
        . ($specs_html ? '<div style="font-size:.85rem;color:rgba(255,255,255,.7);margin-bottom:8px;font-weight:bold">Spezialisierungen:</div><ul style="list-style:none;padding:0;margin:0">' . $specs_html . '</ul>' : '')
        . '</div></div>';
}
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= h($hero['label'] ?? 'Das Team') ?></span>
    <h1><?= nl2br(h($hero['headline'] ?? 'Experten f&uuml;r<br>deine Gesundheit')) ?></h1>
    <p><?= h($hero['subtext'] ?? 'Wir sind stets bem&uuml;ht, Ihnen durch enge Zusammenarbeit unseres Teams eine optimale Betreuung zu gew&auml;hren.') ?></p>
  </div>
</section>

<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <span class="section-label"><?= h($trainers['label'] ?? 'Personal Training') ?></span>
      <h2><?= h($trainers['headline'] ?? 'Unsere Trainer') ?></h2>
    </div>
    <div class="sg3"><?= $trainer_html ?></div>
  </div>
</section>

<section class="section-pad" style="background:var(--white)">
  <div class="container" style="max-width:640px">
    <div class="text-center reveal" style="margin-bottom:32px">
      <span class="section-label"><?= h($physio['label'] ?? 'Physiotherapie') ?></span>
      <h2><?= h($physio['headline'] ?? 'Unser Physio-Team') ?></h2>
      <p style="color:var(--text-secondary)"><?= h($physio['subtext'] ?? 'Kontaktieren Sie den Therapeuten Ihrer Wahl direkt f&uuml;r einen Termin.') ?></p>
    </div>
    <div class="card">
      <div class="card-body" style="padding:24px 32px">
        <?php
        $physio_items = $physio['items'] ?? [
          ['name'=>'Alexander M. Schmidt','phone'=>'+43 1 234567-01'],
          ['name'=>'Lisa M. Becker','phone'=>'+43 1 234567-02'],
          ['name'=>'Gregor W. Weber','phone'=>'+43 1 234567-03'],
          ['name'=>'Jaron I. Fischer','phone'=>'+43 1 234567-04'],
        ];
        foreach ($physio_items as $m): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-light)">
          <span style="font-weight:600"><?= h($m['name'] ?? '') ?></span>
          <?php if (!empty($m['phone'])): ?>
          <a href="tel:<?= h($m['phone']) ?>" class="btn btn-outline-dark" style="font-size:.85rem"><?= h($m['phone']) ?></a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if (!empty($physio['link_url'])): ?>
    <div style="text-align:center;margin-top:24px">
      <a href="<?= h($physio['link_url']) ?>" style="color:var(--gold);font-weight:600"><?= h($physio['link_label'] ?? '&rarr; Mehr zur Physiotherapie') ?></a>
    </div>
    <?php else: ?>
    <div style="text-align:center;margin-top:24px">
      <a href="/physiotherapie" style="color:var(--gold);font-weight:600">&rarr; Mehr zur Physiotherapie bei FlexFit</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= h($cta['headline'] ?? 'Lerne unser Team kennen') ?></h2>
    <p><?= h($cta['subtext'] ?? 'Buche dein kostenloses Probetraining und triff Patrick pers&ouml;nlich.') ?></p>
    <div class="cta-banner-actions">
      <a href="/probetraining" class="btn btn-primary btn-lg"><?= h($cta['btn_primary'] ?? 'Probetraining buchen &rarr;') ?></a>
      <?php if (!empty($cta['btn_secondary'])): ?>
      <a href="/kontakt" class="btn btn-outline btn-lg"><?= h($cta['btn_secondary']) ?></a>
      <?php else: ?>
      <a href="/kontakt" class="btn btn-outline btn-lg">Kontakt aufnehmen</a>
      <?php endif; ?>
    </div>
  </div></div>
</section>

<?php echo render_footer($c); ?>
