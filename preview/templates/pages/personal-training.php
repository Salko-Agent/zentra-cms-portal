<?php
// preview/templates/pages/personal-training.php

$seo     = $c['pages']['personal-training']['seo'] ?? [];
$secs    = $c['pages']['personal-training']['sections'] ?? [];
$hero_d  = $secs['hero']['data']    ?? [];
$reasons = $secs['reasons']['data'] ?? [];
$cta_d   = $secs['cta']['data']     ?? [];

echo render_head(h($seo['title'] ?? 'Personal Training Wien | FlexFit 1080'), $seo);
echo render_navbar($c);

// Reasons / Why-features
$reason_html = '';
foreach ($reasons['items'] ?? [] as $i => $r) {
    $delay = ($i % 4) + 1;
    $reason_html .= '<div class="why-feature reveal reveal-delay-' . $delay . '">'
        . '<div class="why-feature-icon">' . h($r['icon'] ?? '') . '</div>'
        . '<div><div class="why-feature-title">' . h($r['title'] ?? '') . '</div>'
        . '<div class="why-feature-text">' . h($r['text'] ?? '') . '</div></div></div>';
}

$hero_bg  = h($hero_d['bg_image']      ?? '');
$hero_lbl = h($hero_d['label']         ?? 'Personal Training Wien');
$hero_h   = $hero_d['headline']        ?? 'Training bei FlexFit';
$hero_sub = h($hero_d['subtext']       ?? '');
$hero_c1  = h($hero_d['cta_primary']   ?? 'Gratis Probetraining buchen');
$hero_c2  = h($hero_d['cta_secondary'] ?? 'Preise ansehen');
$hero_c2u = h($hero_d['cta_secondary_url'] ?? '#preise');

$reasons_label = h($reasons['label']   ?? 'Warum Personal Training?');
$reasons_head  = $reasons['headline']  ?? 'Der Unterschied zwischen<br>Wollen und Erreichen';
$reasons_sub   = h($reasons['subtext'] ?? '');
$reasons_img   = h($reasons['image']   ?? '');

$cta_head = h($cta_d['headline']      ?? 'Starten Sie heute \u2013 kostenlos & unverbindlich');
$cta_sub  = h($cta_d['subtext']       ?? 'Buchen Sie Ihr gratis Probetraining und erleben Sie selbst, wie FlexFit Sie zu Ihren Zielen bringt.');
$cta_btn1 = h($cta_d['btn_primary']   ?? 'Gratis Probetraining buchen \u2192');
$cta_btn2 = h($cta_d['btn_secondary'] ?? 'Per E-Mail anfragen');
$cta_note = h($cta_d['note']          ?? '');
?>

<section class="page-hero<?= $hero_bg ? ' page-hero--image' : '' ?>"<?= $hero_bg ? ' style="background-image:url(\'' . $hero_bg . '\')"' : '' ?>>
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= $hero_lbl ?></span>
    <h1><?= nl2br(h($hero_h)) ?></h1>
    <p><?= $hero_sub ?></p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:32px">
      <a href="/probetraining" class="btn btn-primary btn-lg"><?= $hero_c1 ?> &rarr;</a>
      <a href="<?= $hero_c2u ?>" class="btn btn-outline btn-lg"><?= $hero_c2 ?></a>
    </div>
  </div>
</section>

<!-- WHY PERSONAL TRAINING -->
<section class="section-pad" style="background:var(--off-white)" id="warum">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center">
      <div class="reveal-left">
        <span class="section-label"><?= $reasons_label ?></span>
        <h2 style="margin-bottom:16px"><?= nl2br(h($reasons_head)) ?></h2>
        <p style="color:var(--text-secondary);margin-bottom:28px"><?= $reasons_sub ?></p>
        <div class="why-features"><?= $reason_html ?></div>
        <blockquote style="margin:32px 0;padding:20px 24px;border-left:4px solid var(--gold);background:var(--white);border-radius:0 var(--r-md) var(--r-md) 0;font-style:italic;color:var(--text-secondary);font-size:.95rem">
          &bdquo;Unser Training ist ideal f&uuml;r vielbesch&auml;ftigte Menschen &ndash; in nur einer Einheit erreichen Sie oft das, wof&uuml;r Sie im Fitnessstudio zwei Einheiten ben&ouml;tigen w&uuml;rden.&ldquo;
          <cite style="display:block;margin-top:8px;font-style:normal;font-weight:700;color:var(--text-primary);font-size:.85rem">&mdash; Patrick K. Miller, Gr&uuml;nder FlexFit</cite>
        </blockquote>
      </div>
      <div class="reveal-right">
        <?php if ($reasons_img): ?>
        <img src="<?= $reasons_img ?>" alt="Personal Training Wien" loading="lazy" style="width:100%;border-radius:var(--r-lg);aspect-ratio:1;object-fit:cover">
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- DESHALB KRAFTTRAINING -->
<section class="benefits-section section-pad">
  <div class="container">
    <div class="benefits-header reveal">
      <span class="section-label" style="justify-content:center">Deshalb Krafttraining</span>
      <h2>Krafttraining grenzt an ein Wundermittel</h2>
      <p style="color:rgba(255,255,255,.65);max-width:600px;margin:16px auto 0;font-size:1rem">Wie aktuelle Studien best&auml;tigen &ndash; Krafttraining ist das wichtigste Training f&uuml;r Ihre Gesundheit</p>
    </div>
    <div class="benefits-grid">
      <?php
      $kft = [
        ['icon'=>'&#x1F4AA;','title'=>'Muskeln aufbauen','text'=>'Krafttraining baut Muskeln auf &amp; macht st&auml;rker &ndash; der wichtigste Schutz gegen altersbedingten Muskelschwund.'],
        ['icon'=>'&#x2764;&#xFE0F;','title'=>'Herz &amp; Immunsystem','text'=>'Wirkt positiv auf Immunsystem, Herz-Kreislauf-System, Gelenke, Knochen &amp; Gehirn.'],
        ['icon'=>'&#x1F9B4;','title'=>'R&uuml;cken &amp; Nacken','text'=>'Lindert oder eliminiert R&uuml;cken- &amp; Nackenschmerzen durch gezieltes Wirbels&auml;ulentraining.'],
        ['icon'=>'&#x1FA7A;','title'=>'Pr&auml;vention','text'=>'Pr&auml;vention gegen Adipositas, Bluthochdruck, Diabetes, Demenz und viele weitere Erkrankungen.'],
        ['icon'=>'&#x23F3;','title'=>'Fit im Alter','text'=>'Krafttraining ist extrem wichtig f&uuml;r Fitness, Selbst&auml;ndigkeit &amp; Lebensqualit&auml;t im Alter.'],
        ['icon'=>'&#x1F31F;','title'=>'Lebensqualit&auml;t','text'=>'Insgesamt erfahren Sie durch individuelles Krafttraining einen deutlichen Anstieg an Lebensqualit&auml;t.'],
      ];
      foreach ($kft as $i => $b): ?>
      <div class="benefit-card reveal reveal-delay-<?= ($i%3)+1 ?>">
        <div class="benefit-icon"><?= $b['icon'] ?></div>
        <h4 class="benefit-title"><?= $b['title'] ?></h4>
        <p class="benefit-text"><?= $b['text'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TRAINER PREISE -->
<section class="section-pad" style="background:var(--off-white)" id="preise">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <span class="section-label">Unser Trainerteam</span>
      <h2>Personal Training bereits ab 69 &euro; / Einheit*</h2>
      <p style="color:var(--text-secondary);max-width:520px;margin:12px auto 0;font-size:.9rem">*Ab-Preise verstehen sich pro Einheit bei Kauf eines 45 min. 10er-Blocks. F&uuml;r detaillierte Preise bitte Wunschtrainer w&auml;hlen.</p>
    </div>
    <div class="sg3">
      <?php
      $trainers_prices = [
        ['name'=>'Patrick K. Miller','focus'=>'Gesundheit, Fitness &amp; Lebensqualit&auml;t','price'=>'ab 86 &euro;','img'=>'/assets/img/team/trainer-1.jpg','specs'=>['Sportwissenschaftler','15+ Jahre Erfahrung','R&uuml;cken &amp; Langlebigkeit'],'featured'=>true],
        ['name'=>'Elias Voggeneder','focus'=>'Muskelaufbau, Kraft &amp; Abnehmen','price'=>'ab 76 &euro;','img'=>'/assets/img/team/elias-voggeneder.jpeg','specs'=>['Staatl. gepr. Fitnesstrainer','Kraft &amp; Kondition'],'featured'=>false],
        ['name'=>'Georgee P. Miller','focus'=>'Gesundheit, Krafttraining &amp; K&ouml;rperformung','price'=>'ab 69 &euro;','img'=>'/assets/img/team/trainer-2.jpg','specs'=>['Staatl. gepr. Fitnesstrainer','Gesundheit &amp; Rehabilitation'],'featured'=>false],
      ];
      foreach ($trainers_prices as $i => $tr): ?>
      <div class="card reveal reveal-delay-<?= $i+1 ?>">
        <div class="card-body" style="padding:28px;text-align:center;flex:1;display:flex;flex-direction:column">
          <img src="<?= $tr['img'] ?>" alt="<?= $tr['name'] ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;object-position:top;margin:0 auto 16px;display:block;border:3px solid var(--gold)">
          <h3 style="margin-bottom:4px;font-size:1.15rem"><?= $tr['name'] ?></h3>
          <div style="font-size:.85rem;color:var(--text-secondary);margin-bottom:12px"><?= $tr['focus'] ?></div>
          <div style="font-family:var(--font-display);font-size:2.2rem;font-weight:700;color:var(--gold);line-height:1;margin-bottom:4px"><?= $tr['price'] ?></div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:20px">/ Training*</div>
          <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:24px;text-align:left">
            <?php foreach ($tr['specs'] as $s): ?>
            <div style="display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--text-secondary)"><span style="color:var(--gold)">&#10003;</span> <?= $s ?></div>
            <?php endforeach; ?>
          </div>
          <a href="/probetraining" class="btn <?= $i===0?'btn-primary':'btn-outline-dark' ?>" style="width:100%;justify-content:center;margin-top:auto">Probetraining buchen</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Gruppentraining -->
    <div style="margin-top:48px;padding:32px;background:var(--white);border-radius:var(--r-lg);border:1px solid var(--border-light)">
      <div class="sg2-md" style="align-items:center">
        <div>
          <h3 style="margin-bottom:12px">Gruppentraining &ndash; ab 40 &euro; / Person</h3>
          <p style="color:var(--text-secondary);margin-bottom:16px">Sie trainieren lieber in der Gruppe? Motivieren Sie Ihre Freund:innen und profitieren Sie von attraktiven Gruppenangeboten!</p>
          <div style="display:flex;flex-wrap:wrap;gap:12px">
            <?php foreach (['2 Personen: 60 &euro; p.P.','3 Personen: 50 &euro; p.P.','4 Personen: 40 &euro; p.P.'] as $g): ?>
            <span style="padding:8px 16px;background:var(--off-white);border-radius:var(--r-md);font-weight:600;font-size:.9rem;color:var(--text-primary)"><?= $g ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div style="text-align:center">
          <a href="/probetraining" class="btn btn-primary btn-lg">Probetraining buchen &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= $cta_head ?></h2>
    <p><?= $cta_sub ?></p>
    <div class="cta-banner-actions">
      <a href="/probetraining" class="btn btn-primary btn-lg"><?= $cta_btn1 ?></a>
      <a href="/kontakt" class="btn btn-outline btn-lg"><?= $cta_btn2 ?></a>
    </div>
    <?php if ($cta_note): ?><p class="cta-note"><?= $cta_note ?></p><?php endif; ?>
  </div></div>
</section>

<?php echo render_footer($c); ?>
