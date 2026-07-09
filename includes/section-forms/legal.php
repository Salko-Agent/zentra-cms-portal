<?php
// includes/section-forms/legal.php  (impressum / datenschutz content)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows
$decoded_items = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $decoded_items[] = $obj;
}

// Determine privacy sections: from field 'sections' or from decoded items
$privacy_secs = [];
if (!empty($d['sections'])) {
    $privacy_secs = is_string($d['sections']) ? (json_decode($d['sections'], true) ?: []) : $d['sections'];
}
if (empty($privacy_secs) && !empty($decoded_items)) {
    $privacy_secs = $decoded_items;
}
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  // Impressum fields
  if (isset($d['person'])):
    sf_text('person',      'Inhaber Name',   $d['person']      ?? '');
    sf_text('person_role', 'Rolle',          $d['person_role'] ?? '');
    sf_text('company',     'Firma',          $d['company']     ?? '');
    sf_textarea('address', 'Adresse',        $d['address']     ?? '');
    sf_text('phone',       'Telefon',        $d['phone']       ?? '');
    sf_text('email',       'E-Mail',         $d['email']       ?? '');
    sf_textarea('nutzungsbedingungen', 'Nutzungsbedingungen / Haftung', $d['nutzungsbedingungen'] ?? '');
    sf_textarea('analytics_text', 'Webanalyse-Text', $d['analytics_text'] ?? '');
  endif;
  // Datenschutz fields
  if (isset($d['intro'])):
    sf_textarea('intro', 'Einleitung', $d['intro'] ?? '');
  endif;
  ?>
  <?php if (!empty($privacy_secs)): ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Abschnitte</p>
  <div id="legalRepeater">
  <?php foreach ($privacy_secs as $i => $sec_item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($sec_item['title'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-group"><label>Titel</label>
      <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($sec_item['title'] ?? '') ?>"></div>
    <div class="form-group"><label>Text</label>
      <textarea name="items[<?= $i ?>][text]"><?= htmlspecialchars($sec_item['text'] ?? '') ?></textarea></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Abschnitt hinzufügen</button>
  <?php endif; ?>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
