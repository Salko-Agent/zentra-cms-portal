<?php
// includes/section-forms/cta.php
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',   $d['subtext']  ?? '');
  sf_text('btn_primary',     'Button 1 Text', $d['btn_primary']     ?? '');
  sf_text('btn_primary_url', 'Button 1 URL',  $d['btn_primary_url'] ?? '', 'z.B. /probetraining');
  sf_text('btn_secondary',     'Button 2 Text', $d['btn_secondary']     ?? '');
  sf_text('btn_secondary_url', 'Button 2 URL',  $d['btn_secondary_url'] ?? '', 'z.B. /kontakt');
  sf_text('note',          'Notiz (Kleingedrucktes)', $d['note'] ?? '');
  ?>
  <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
</form>
