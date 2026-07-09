<?php
// preview/templates/pages/physiotherapie.php

$seo   = $c['pages']['physiotherapie']['seo'] ?? [];
$secs  = $c['pages']['physiotherapie']['sections'] ?? [];
$hero  = $secs['hero']['data']       ?? [];
$team  = $secs['team']['data']       ?? [];
$leist = $secs['leistungen']['data'] ?? [];
$preis = $secs['preise']['data']     ?? [];
$ablauf= $secs['ablauf']['data']     ?? [];
$cta   = $secs['cta']['data']        ?? [];

echo render_head(h($seo['title'] ?? 'Physiotherapie Wien 1080 | FlexFit'), $seo);
echo render_navbar($c);

// Team members
$team_html = '';
foreach ($team['items'] ?? [] as $m) {
    if (!isset($m['name'])) continue;
    $photo   = !empty($m['photo']) ? '<img src="' . h($m['photo']) . '" alt="' . h($m['name']) . '" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover">' : '<div style="background:var(--gold);width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:3rem;font-weight:900;color:var(--black)">' . h($m['initials'] ?? '??') . '</div>';
    $phone   = !empty($m['phone']) ? '<a href="tel:' . h($m['phone']) . '" class="btn btn-primary btn-sm" style="margin-top:12px">' . h($m['phone']) . '</a>' : '';
    $team_html .= '<div class="card" style="overflow:hidden;text-align:center">'
        . '<div style="overflow:hidden;border-radius:var(--r-md) var(--r-md) 0 0">' . $photo . '</div>'
        . '<div style="padding:20px">'
        . '<div style="font-weight:700;font-size:1.1rem">' . h($m['name']) . '</div>'
        . $phone . '</div></div>';
}

// Leistungen
$leist_items_raw = $leist['items'] ?? [];
if (is_string($leist_items_raw)) $leist_items_raw = json_decode($leist_items_raw, true) ?: [];
$leist_html = '';
foreach ($leist_items_raw as $item) {
    $leist_html .= '<div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border-light)">'
        . '<span style="color:var(--gold)">✓</span>'
        . '<span>' . h($item) . '</span></div>';
}
$spez_raw = $leist['spezialisierungen'] ?? [];
if (is_string($spez_raw)) {
    $decoded = json_decode($spez_raw, true);
    $spez_raw = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $spez_raw))));
}
$spez_html = '';
foreach ($spez_raw as $s) {
    $spez_html .= '<li style="padding:8px 0;border-bottom:1px solid var(--border-light)">' . h($s) . '</li>';
}

// Preise
$preis_html = '';
foreach ($preis['items'] ?? [] as $row) {
    $preis_html .= '<div style="display:flex;justify-content:space-between;align-items:center;padding:18px 24px;border-bottom:1px solid var(--border-light)">'
        . '<span style="font-weight:600">' . h($row['label'] ?? $row['title'] ?? '') . '</span>'
        . '<span style="font-family:var(--font-display);font-size:1.5rem;font-weight:900;color:var(--gold)">' . h($row['price'] ?? '') . '</span></div>';
}

// Ablauf steps
$steps_raw = $ablauf['items'] ?? [];
$steps_html = '';
foreach ($steps_raw as $i => $step) {
    $text = is_array($step) ? ($step['text'] ?? '') : $step;
    $steps_html .= '<div style="display:flex;gap:20px;padding:20px;background:var(--white);border-radius:var(--r-md);border:1px solid var(--border-light);margin-bottom:12px">'
        . '<div style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:var(--gold);line-height:1;min-width:44px">0' . ($i + 1) . '</div>'
        . '<div style="font-size:.9rem;color:var(--text-secondary)">' . h($text) . '</div></div>';
}
$mitbringen_raw = $ablauf['mitbringen'] ?? [];
if (is_string($mitbringen_raw)) {
    $decoded = json_decode($mitbringen_raw, true);
    $mitbringen_raw = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $mitbringen_raw))));
}
$mitbringen_html = '';
foreach ($mitbringen_raw as $m) {
    $mitbringen_html .= '<li style="padding:6px 0;display:flex;gap:10px"><span style="color:var(--gold)">✓</span>' . h($m) . '</li>';
}
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px"><?= h($hero['label'] ?? '') ?></span>
    <h1><?= nl2br(h($hero['headline'] ?? '')) ?></h1>
    <p><?= h($hero['subtext'] ?? '') ?></p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:32px">
      <a href="<?= h($hero['cta_primary_url'] ?? '/kontakt') ?>" class="btn btn-primary btn-lg"><?= h($hero['cta_primary'] ?? 'Termin anfragen') ?> →</a>
    </div>
  </div>
</section>

<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <span class="section-label"><?= h($team['label'] ?? '') ?></span>
      <h2><?= h($team['headline'] ?? '') ?></h2>
      <p style="color:var(--text-secondary);max-width:560px;margin:16px auto 0"><?= h($team['subtext'] ?? '') ?></p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px"><?= $team_html ?></div>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start">
      <div class="reveal-left">
        <?php if (!empty($leist['image'])): ?>
        <img src="<?= h($leist['image']) ?>" alt="Physiotherapie" loading="lazy" style="width:100%;border-radius:var(--r-lg);aspect-ratio:4/3;object-fit:cover;margin-bottom:32px">
        <?php endif; ?>
        <span class="section-label"><?= h($leist['label'] ?? '') ?></span>
        <h2 style="margin-bottom:20px"><?= h($leist['headline'] ?? '') ?></h2>
        <?= $leist_html ?>
      </div>
      <div class="reveal-right">
        <span class="section-label"><?= h($leist['spezialisierungen_label'] ?? 'Spezialisierungen') ?></span>
        <ul style="list-style:none;padding:0;margin:20px 0 32px"><?= $spez_html ?></ul>
        <div class="card" style="overflow:hidden">
          <div style="padding:20px 24px 0;font-weight:700">Preise</div>
          <?= $preis_html ?>
          <?php if (!empty($preis['footnote'])): ?>
          <p style="font-size:.8rem;color:var(--text-muted);padding:16px 24px"><?= h($preis['footnote']) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div style="max-width:760px;margin:0 auto">
      <span class="section-label"><?= h($ablauf['label'] ?? '') ?></span>
      <h2 style="margin-bottom:32px"><?= h($ablauf['headline'] ?? '') ?></h2>
      <?= $steps_html ?>
      <?php if ($mitbringen_html): ?>
      <div style="background:var(--white);border-radius:var(--r-md);padding:24px;margin-top:32px;border:1px solid var(--border-light)">
        <p style="font-weight:700;margin-bottom:12px"><?= h($ablauf['mitbringen_headline'] ?? 'Zur ersten Einheit bitte mitbringen:') ?></p>
        <ul style="list-style:none;padding:0"><?= $mitbringen_html ?></ul>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <h2><?= h($cta['headline'] ?? '') ?></h2>
    <p><?= h($cta['subtext'] ?? '') ?></p>
    <div class="cta-banner-actions">
      <a href="/kontakt" class="btn btn-primary btn-lg"><?= h($cta['btn_primary'] ?? 'Kontakt aufnehmen →') ?></a>
    </div>
    <?php if (!empty($cta['note'])): ?>
    <p class="cta-note"><?= h($cta['note']) ?></p>
    <?php endif; ?>
  </div></div>
</section>

<?php echo render_footer($c); ?>
