<?php
// includes/section-forms/trainer_profile.php
// Single trainer profile (used for home page trainer section)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;
$items = $sec_items;
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <!-- Signal to api/save.php that this form manages items (allows clearing all specs) -->
  <input type="hidden" name="items_submitted" value="1">
  <!-- Clear the legacy specs JSON field once items are in section_items -->
  <?php if (!empty($items)): ?>
  <input type="hidden" name="fields[specs]" value="">
  <?php endif; ?>

  <?php
  sf_text('name',        'Name',            $d['name']        ?? '');
  sf_text('title',       'Titel / Rolle',   $d['title']       ?? '');
  sf_text('credentials', 'Qualifikationen', $d['credentials'] ?? '', 'Kommasepariert');
  sf_text('experience',  'Erfahrung',       $d['experience']  ?? '');
  sf_textarea('bio',     'Bio (Absatz 1)',  $d['bio']         ?? '');
  sf_textarea('bio2',    'Bio (Absatz 2)',  $d['bio2']        ?? '');
  sf_image('photo',      'Foto URL',        $d['photo']       ?? '');
  ?>

  <!-- Specs repeater (icon / label / value) -->
  <div class="form-group">
    <label style="font-weight:700">Kennzahlen / Specs</label>
    <div id="specsRepeater">
    <?php
    // Prefer section_items (the authoritative store after first save).
    // Fall back to the legacy JSON field only when section_items is empty.
    $specs = [];
    if (!empty($items)) {
        foreach ($items as $it) {
            $obj = json_decode($it['item_json'] ?? '{}', true);
            if ($obj) $specs[] = $obj;
        }
    } elseif (!empty($d['specs'])) {
        $raw = is_string($d['specs']) ? json_decode($d['specs'], true) : $d['specs'];
        if (is_array($raw)) $specs = $raw;
    }
    foreach ($specs as $i => $sp):
    ?>
      <div class="repeater-row" style="border:1px solid var(--border);border-radius:var(--r-sm,6px);padding:14px;margin-bottom:10px;position:relative">
        <div style="display:flex;justify-content:flex-end;margin-bottom:6px">
          <button type="button" onclick="this.closest('.repeater-row').remove()" style="background:none;border:none;cursor:pointer;color:var(--danger,#f87171);font-size:.9rem" title="Entfernen">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:60px 1fr 1fr;gap:8px">
          <input type="text" name="items[<?= $i ?>][icon]"  value="<?= htmlspecialchars($sp['icon']  ?? '') ?>" placeholder="Icon" style="text-align:center">
          <input type="text" name="items[<?= $i ?>][label]" value="<?= htmlspecialchars($sp['label'] ?? '') ?>" placeholder="Label">
          <input type="text" name="items[<?= $i ?>][value]" value="<?= htmlspecialchars($sp['value'] ?? '') ?>" placeholder="Wert">
        </div>
      </div>
    <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-secondary btn-sm" onclick="addSpecRow()">+ Spec hinzufügen</button>
  </div>

  <!-- Specializations (line-separated) -->
  <?php
  $specializations = $d['specializations'] ?? '';
  if (is_string($specializations) && $specializations !== '') {
      $decoded = json_decode($specializations, true);
      if (is_array($decoded)) $specializations = implode("\n", $decoded);
  } elseif (is_array($specializations)) {
      $specializations = implode("\n", $specializations);
  }
  ?>
  <div class="form-group">
    <label>Spezialisierungen (eine pro Zeile)</label>
    <textarea name="fields[specializations]" rows="5" placeholder="z.B. Muskelaufbau & Hypertrophie&#10;Fettabbau & Körperkomposition"><?= htmlspecialchars($specializations) ?></textarea>
  </div>

  <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
</form>

<script>
function addSpecRow() {
  var c = document.getElementById('specsRepeater');
  var idx = c.querySelectorAll('.repeater-row').length;
  var html = '<div class="repeater-row" style="border:1px solid var(--border);border-radius:var(--r-sm,6px);padding:14px;margin-bottom:10px;position:relative">'
    + '<div style="display:flex;justify-content:flex-end;margin-bottom:6px">'
    + '<button type="button" onclick="this.closest(\'.repeater-row\').remove()" style="background:none;border:none;cursor:pointer;color:var(--danger,#f87171);font-size:.9rem" title="Entfernen">✕</button>'
    + '</div>'
    + '<div style="display:grid;grid-template-columns:60px 1fr 1fr;gap:8px">'
    + '<input type="text" name="items[' + idx + '][icon]" placeholder="Icon" style="text-align:center">'
    + '<input type="text" name="items[' + idx + '][label]" placeholder="Label">'
    + '<input type="text" name="items[' + idx + '][value]" placeholder="Wert">'
    + '</div></div>';
  c.insertAdjacentHTML('beforeend', html);
}
</script>
