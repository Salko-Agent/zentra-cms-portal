<?php
// includes/section-forms/contact_info.php
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_textarea('headline', 'Überschrift',  $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',    $d['subtext']  ?? '');
  sf_text('address',       'Adresse',      $d['address']  ?? '');
  sf_text('email',         'E-Mail',       $d['email']    ?? '');
  sf_text('phone',         'Telefon',      $d['phone']    ?? '');
  sf_text('hours',         'Öffnungszeiten (Mo–Fr)',      $d['hours']         ?? '');
  sf_text('hours_weekend', 'Öffnungszeiten (Wochenende)', $d['hours_weekend'] ?? '');
  ?>
  <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
</form>
