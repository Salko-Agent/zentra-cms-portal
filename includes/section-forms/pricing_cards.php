<?php
// includes/section-forms/pricing_cards.php
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows
$rows = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $rows[] = $obj;
}
if (empty($rows) && !empty($d['items'])) {
    $arr = is_string($d['items']) ? json_decode($d['items'], true) : $d['items'];
    if (is_array($arr)) $rows = $arr;
}
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_text('label',    'Badge-Text', $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Preis-Karten</p>
  <div id="priceRepeater">
  <?php foreach ($rows as $i => $item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($item['title'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 70px"><label>Icon</label>
        <input type="text" name="items[<?= $i ?>][icon]" value="<?= htmlspecialchars($item['icon'] ?? '') ?>" style="text-align:center"></div>
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($item['title'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Preis</label>
        <input type="text" name="items[<?= $i ?>][price]" value="<?= htmlspecialchars($item['price'] ?? '') ?>" placeholder="ab 69 €"></div>
      <div class="form-group"><label>Einheit</label>
        <input type="text" name="items[<?= $i ?>][price_unit]" value="<?= htmlspecialchars($item['price_unit'] ?? '') ?>" placeholder="/ Einheit"></div>
    </div>
    <div class="form-group"><label>Badge (optional)</label>
      <input type="text" name="items[<?= $i ?>][badge]" value="<?= htmlspecialchars($item['badge'] ?? '') ?>" placeholder="z.B. Beliebteste Option"></div>
    <div class="form-group"><label>Features (eine pro Zeile)</label>
      <textarea name="items[<?= $i ?>][features]"><?php
        $f = $item['features'] ?? '';
        if (is_array($f)) echo htmlspecialchars(implode("\n", $f));
        else echo htmlspecialchars($f);
      ?></textarea></div>
    <div class="form-group" style="display:flex;align-items:center;gap:8px">
      <label class="toggle" style="margin-bottom:0">
        <input type="checkbox" name="items[<?= $i ?>][featured]" value="1" <?= !empty($item['featured']) ? 'checked' : '' ?>>
        <span class="toggle-slider"></span>
      </label>
      <span style="font-size:.82rem;color:var(--text-muted)">Hervorgehoben (gold border)</span>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Karte hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
