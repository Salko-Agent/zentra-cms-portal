<?php
// includes/section-forms/image_text.php  (Bild & Text / two-col layouts)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;
$items = $sec_items; // repeater items from section_items table
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_text('label',    'Badge-Text',   $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',   $d['subtext']  ?? $d['text'] ?? '');
  sf_image('image',       'Bild URL',    $d['image']    ?? $d['photo'] ?? '');
  sf_text('image_badge_number', 'Bild-Badge Zahl',  $d['image_badge_number'] ?? '', 'z.B. "15+"');
  sf_text('image_badge_label',  'Bild-Badge Label', $d['image_badge_label']  ?? '', 'z.B. "Jahre Erfahrung"');
  ?>

  <!-- Items Repeater (icon/title/text) -->
  <div class="form-group">
    <label style="font-weight:700">Einträge</label>
    <div id="imgTextRepeater">
    <?php
    $rows = [];
    foreach ($items as $it) {
        $obj = json_decode($it['item_json'] ?? '{}', true);
        if ($obj) $rows[] = $obj;
    }
    // Fallback: items may be stored as JSON field under 'items' or 'features'
    if (empty($rows)) {
        foreach (['items', 'features'] as $_fk) {
            if (!empty($d[$_fk])) {
                $arr = is_string($d[$_fk]) ? json_decode($d[$_fk], true) : $d[$_fk];
                if (is_array($arr)) { $rows = $arr; break; }
            }
        }
    }
    foreach ($rows as $i => $row):
        if (is_string($row)) {
            $icon = ''; $title = ''; $text = htmlspecialchars($row);
        } else {
            $icon  = htmlspecialchars($row['icon']  ?? '');
            $title = htmlspecialchars($row['title'] ?? '');
            $text  = htmlspecialchars($row['text']  ?? '');
        }
    ?>
      <div class="repeater-row" style="border:1px solid var(--border);border-radius:var(--r-sm,6px);padding:14px;margin-bottom:10px;position:relative">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
          <strong style="font-size:.82rem"><?= $title ?: 'Eintrag ' . ($i+1) ?></strong>
          <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()" style="background:none;border:none;cursor:pointer;color:var(--danger,#f87171);font-size:.9rem" title="Entfernen">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:60px 1fr;gap:8px;margin-bottom:8px">
          <input type="text" name="items[<?= $i ?>][icon]"  value="<?= $icon ?>"  placeholder="Icon" style="text-align:center">
          <input type="text" name="items[<?= $i ?>][title]" value="<?= $title ?>" placeholder="Titel">
        </div>
        <textarea name="items[<?= $i ?>][text]" placeholder="Beschreibung" rows="2"><?= $text ?></textarea>
      </div>
    <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-secondary btn-sm" onclick="addImgTextRow()">+ Eintrag hinzufügen</button>
  </div>

  <!-- Goals (line-separated, stored as JSON) -->
  <?php
  $goals = $d['goals'] ?? '';
  if (is_string($goals) && $goals !== '') {
      $decoded = json_decode($goals, true);
      if (is_array($decoded)) $goals = implode("\n", $decoded);
  } elseif (is_array($goals)) {
      $goals = implode("\n", $goals);
  }
  ?>
  <div class="form-group">
    <label>Badges / Ziele (eine pro Zeile)</label>
    <textarea name="fields[goals]" rows="3" placeholder="z.B. Abnehmen&#10;Muskelaufbau"><?= htmlspecialchars($goals) ?></textarea>
    <div class="form-hint">Werden als kleine Badges angezeigt. Leer lassen wenn nicht benötigt.</div>
  </div>

  <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
</form>

<script>
function addImgTextRow() {
  var c = document.getElementById('imgTextRepeater');
  var idx = c.querySelectorAll('.repeater-row').length;
  var html = '<div class="repeater-row" style="border:1px solid var(--border);border-radius:var(--r-sm,6px);padding:14px;margin-bottom:10px;position:relative">'
    + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">'
    + '<strong style="font-size:.82rem">Neuer Eintrag</strong>'
    + '<button type="button" class="btn-remove-row" onclick="this.closest(\'.repeater-row\').remove()" style="background:none;border:none;cursor:pointer;color:var(--danger,#f87171);font-size:.9rem" title="Entfernen">✕</button>'
    + '</div>'
    + '<div style="display:grid;grid-template-columns:60px 1fr;gap:8px;margin-bottom:8px">'
    + '<input type="text" name="items[' + idx + '][icon]" placeholder="Icon" style="text-align:center">'
    + '<input type="text" name="items[' + idx + '][title]" placeholder="Titel">'
    + '</div>'
    + '<textarea name="items[' + idx + '][text]" placeholder="Beschreibung" rows="2"></textarea>'
    + '</div>';
  c.insertAdjacentHTML('beforeend', html);
}
</script>
