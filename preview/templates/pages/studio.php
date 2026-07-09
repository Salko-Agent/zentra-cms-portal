<?php
// preview/templates/pages/studio.php

$seo   = $c['pages']['studio']['seo'] ?? [];
$secs  = $c['pages']['studio']['sections'] ?? [];
$hero  = $secs['hero']['data']          ?? [];
$feat  = $secs['features']['data']      ?? [];
$erreich = $secs['erreichbarkeit']['data'] ?? [];
$raum  = $secs['raumvermietung']['data'] ?? [];
$cta   = $secs['cta']['data']           ?? [];

echo render_head(h($seo['title'] ?? 'Unser Studio | FlexFit 1080 Wien'), $seo);
echo render_navbar($c);

$feat_items_raw = $feat['items'] ?? [];
if (is_string($feat_items_raw)) $feat_items_raw = json_decode($feat_items_raw, true) ?: [];
$feat_html = '';
foreach ($feat_items_raw as $item) {
    $feat_html .= '<div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.1)">'
        . '<span style="color:var(--gold);font-size:1.1rem">✓</span>'
        . '<span style="color:rgba(255,255,255,.85)">' . h($item) . '</span></div>';
}

$erreich_html = '';
foreach ($erreich['items'] ?? [] as $item) {
    $erreich_html .= '<div style="display:flex;gap:16px;padding:20px;background:var(--white);border-radius:var(--r-md);border:1px solid var(--border-light)">'
        . '<div style="font-size:1.6rem">' . h($item['icon'] ?? '') . '</div>'
        . '<div><div style="font-weight:700;margin-bottom:4px">' . h($item['title'] ?? '') . '</div>'
        . '<div style="font-size:.88rem;color:var(--text-secondary)">' . h($item['text'] ?? '') . '</div></div></div>';
}

$rooms_html = '';
foreach ($raum['items'] ?? [] as $room) {
    $prices_html = '';
    $prices_raw = $room['prices'] ?? [];
    // Support both array-of-objects and "label | price" newline-delimited string
    if (is_string($prices_raw)) {
        $lines = array_filter(array_map('trim', explode("\n", $prices_raw)));
        $prices_raw = [];
        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            $prices_raw[] = ['label' => $parts[0] ?? '', 'price' => $parts[1] ?? ''];
        }
    }
    foreach ($prices_raw as $p) {
        $prices_html .= '<div style="display:flex;justify-content:space-between;padding:10px 0;border-top:1px solid var(--border-light)">'
            . '<span style="font-size:.88rem">' . h($p['label'] ?? '') . '</span>'
            . '<span style="font-weight:700;color:var(--gold)">' . h($p['price'] ?? '') . '</span></div>';
    }
    $rooms_html .= '<div class="card" style="padding:32px">'
        . '<div style="font-size:2.5rem;margin-bottom:12px">' . h($room['icon'] ?? '') . '</div>'
        . '<h3 style="margin-bottom:12px">' . h($room['title'] ?? '') . '</h3>'
        . '<p style="font-size:.9rem;color:var(--text-secondary);margin-bottom:20px">' . h($room['description'] ?? '') . '</p>'
        . $prices_html
        . '<p style="font-size:.78rem;color:var(--text-muted);margin-top:12px">' . h($room['footnote'] ?? '') . '</p>'
        . '<a href="/kontakt" class="btn btn-outline-dark btn-sm" style="margin-top:20px">Anfragen →</a></div>';
}

$vorteile_raw = $raum['vorteile'] ?? [];
if (is_string($vorteile_raw)) {
    $decoded = json_decode($vorteile_raw, true);
    $vorteile_raw = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $vorteile_raw))));
}
$vorteile_html = '';
foreach ($vorteile_raw as $v) {
    $vorteile_html .= '<div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light)">'
        . '<span style="color:var(--gold)">✓</span><span>' . h($v) . '</span></div>';
}
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= h($hero['label'] ?? '') ?></span>
    <h1><?= nl2br(h($hero['headline'] ?? '')) ?></h1>
    <p><?= h($hero['subtext'] ?? '') ?></p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:32px">
      <a href="/probetraining" class="btn btn-primary btn-lg"><?= h($hero['cta_primary'] ?? 'Probetraining buchen') ?> →</a>
      <a href="<?= h($hero['cta_secondary_url'] ?? '#mieten') ?>" class="btn btn-outline btn-lg"><?= h($hero['cta_secondary'] ?? 'Räume mieten') ?></a>
    </div>
  </div>
</section>

<section class="trainer-section section-pad">
  <div class="container">
    <div class="trainer-grid">
      <div class="trainer-image-wrap reveal-left">
        <?php if (!empty($feat['image'])): ?>
        <img src="<?= h($feat['image']) ?>" alt="FlexFit Studio" loading="lazy" style="width:100%;height:100%;object-fit:cover">
        <?php endif; ?>
      </div>
      <div class="trainer-content reveal-right">
        <span class="section-label"><?= h($feat['label'] ?? '') ?></span>
        <h2 class="trainer-name"><?= nl2br(h($feat['headline'] ?? '')) ?></h2>
        <p style="color:rgba(255,255,255,.7);margin-bottom:28px"><?= h($feat['subtext'] ?? '') ?></p>
        <?= $feat_html ?>
      </div>
    </div>
  </div>
</section>

<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <span class="section-label"><?= h($erreich['label'] ?? 'Erreichbarkeit') ?></span>
      <h2><?= h($erreich['headline'] ?? 'Zentral in Wien 1080') ?></h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px"><?= $erreich_html ?></div>
    <div style="text-align:center">
      <a href="<?= h($c['site']['maps_url'] ?? '#') ?>" target="_blank" class="btn btn-primary"><?= h($erreich['maps_btn'] ?? 'Auf Google Maps öffnen →') ?></a>
    </div>
  </div>
</section>

<section class="section-pad" id="mieten">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <span class="section-label"><?= h($raum['label'] ?? '') ?></span>
      <h2><?= h($raum['headline'] ?? '') ?></h2>
      <p style="color:var(--text-secondary);max-width:560px;margin:16px auto 0"><?= h($raum['subtext'] ?? '') ?></p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:48px"><?= $rooms_html ?></div>
    <?php if ($vorteile_html): ?>
    <div style="max-width:560px;margin:0 auto">
      <h3 style="margin-bottom:20px;text-align:center">Ihre Vorteile</h3>
      <?= $vorteile_html ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= h($cta['headline'] ?? '') ?></h2>
    <p><?= h($cta['subtext'] ?? '') ?></p>
    <div class="cta-banner-actions">
      <a href="/kontakt" class="btn btn-primary btn-lg"><?= h($cta['btn_primary'] ?? 'Besichtigungstermin anfragen →') ?></a>
    </div>
  </div></div>
</section>

<?php echo render_footer($c); ?>
