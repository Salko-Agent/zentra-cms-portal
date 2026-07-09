<?php
// includes/section-forms/room_listing.php
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows
$rows = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $rows[] = $obj;
}
// Fallback: rooms stored as field
if (empty($rows) && !empty($d['rooms'])) {
    $arr = is_string($d['rooms']) ? json_decode($d['rooms'], true) : $d['rooms'];
    if (is_array($arr)) $rows = $arr;
}
if (empty($rows) && !empty($d['items'])) {
    $arr = is_string($d['items']) ? json_decode($d['items'], true) : $d['items'];
    if (is_array($arr)) $rows = $arr;
}

// Vorteile (line-separated or JSON array)
$vorteile = $d['vorteile'] ?? '';
if (is_string($vorteile) && $vorteile !== '') {
    $decoded = json_decode($vorteile, true);
    if (is_array($decoded)) $vorteile = implode("\n", $decoded);
} elseif (is_array($vorteile)) {
    $vorteile = implode("\n", $vorteile);
}
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_text('label',    'Badge-Text',  $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',   $d['subtext']  ?? '');
  ?>

  <!-- Vorteile (line-separated) -->
  <div class="form-group">
    <label>Vorteile (eine pro Zeile)</label>
    <textarea name="fields[vorteile]" rows="4" placeholder="z.B. 24/7 Zugang&#10;Klimatisiert"><?= htmlspecialchars($vorteile) ?></textarea>
    <div class="form-hint">Werden als Checkmark-Liste angezeigt. Leer lassen wenn nicht benötigt.</div>
  </div>

  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Räume</p>
  <div id="roomRepeater">
  <?php foreach ($rows as $i => $room): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($room['title'] ?? $room['name'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 70px"><label>Icon</label>
        <input type="text" name="items[<?= $i ?>][icon]" value="<?= htmlspecialchars($room['icon'] ?? '') ?>" style="text-align:center"></div>
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($room['title'] ?? $room['name'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Beschreibung</label>
      <textarea name="items[<?= $i ?>][description]"><?= htmlspecialchars($room['description'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Preise (eine pro Zeile: Bezeichnung | Preis)</label>
      <textarea name="items[<?= $i ?>][prices]" rows="3" placeholder="Stundensatz | € 35,-&#10;Monatlich | € 170,-"><?php
        $prices = $room['prices'] ?? [];
        if (is_array($prices)) {
            $lines = [];
            foreach ($prices as $p) {
                if (is_array($p)) $lines[] = ($p['label'] ?? '') . ' | ' . ($p['price'] ?? '');
                elseif (is_string($p)) $lines[] = $p;
            }
            echo htmlspecialchars(implode("\n", $lines));
        } elseif (is_string($prices)) {
            echo htmlspecialchars($prices);
        }
      ?></textarea>
      <div class="form-hint">Format: Bezeichnung | Preis (z.B. "Stundensatz | € 35,-")</div>
    </div>
    <div class="form-group"><label>Fußnote</label>
      <input type="text" name="items[<?= $i ?>][footnote]" value="<?= htmlspecialchars($room['footnote'] ?? '') ?>" placeholder="z.B. Mindestbuchung 3 Monate"></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Raum hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
