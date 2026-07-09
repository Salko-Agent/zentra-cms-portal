<?php
// includes/section-forms/hero.php
// Supports both home hero (badge, headline_line1/2, subheadline, trust, rating)
// and subpage hero (label, headline, subtext)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Detect which variant: home hero has 'badge' or 'headline_line1'
$is_home = isset($d['badge']) || isset($d['headline_line1']) || isset($d['price_note']);
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">

  <?php if ($is_home): ?>
  <!-- HOME Hero fields -->
  <?php
  sf_text('badge',          'Badge-Text (Adresse/Ort)',   $d['badge']          ?? '');
  sf_text('headline_line1', 'Untertitel (unter FlexFit-Logo)', $d['headline_line1'] ?? '', 'z.B. "Personal Training Wien"');
  sf_textarea('subheadline','Haupttext / USP',            $d['subheadline']    ?? '');
  sf_text('price_note',     'Preis-Hinweis',              $d['price_note']     ?? '', 'z.B. "Bereits ab 69 € / Einheit"');
  sf_text('cta_primary',     'Button 1 Text',             $d['cta_primary']     ?? '');
  sf_text('cta_primary_url', 'Button 1 URL',              $d['cta_primary_url'] ?? '');
  sf_text('cta_secondary',     'Button 2 Text',           $d['cta_secondary']     ?? '');
  sf_text('cta_secondary_url', 'Button 2 URL',            $d['cta_secondary_url'] ?? '');
  sf_text('rating',         'Google Rating',              $d['rating']         ?? '', 'z.B. "4.9"');
  sf_text('trust_count',    'Anzahl Bewertungen',         $d['trust_count']    ?? '', 'z.B. "65"');
  sf_text('trust_text',     'Trust-Text (optional, auto-generiert wenn leer)', $d['trust_text'] ?? '', 'z.B. "<strong>4.9/5</strong> – 65 Google-Bewertungen"');
  sf_image('bg_image',      'Hintergrundbild URL', $d['bg_image']      ?? '');
  ?>
  <?php else: ?>
  <!-- SUBPAGE Hero fields -->
  <?php
  sf_text('label',    'Badge-Text',  $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift (HTML: <br> erlaubt)', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext', $d['subtext']  ?? '');
  sf_text('cta_primary',     'Button 1 Text', $d['cta_primary']     ?? '');
  sf_text('cta_primary_url', 'Button 1 URL',  $d['cta_primary_url'] ?? '');
  sf_text('cta_secondary',     'Button 2 Text', $d['cta_secondary']     ?? '');
  sf_text('cta_secondary_url', 'Button 2 URL',  $d['cta_secondary_url'] ?? '');
  sf_image('bg_image', 'Hintergrundbild URL', $d['bg_image'] ?? '');
  ?>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
</form>
