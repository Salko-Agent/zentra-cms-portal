<?php
// includes/section-forms/price_table.php
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
  sf_text('label',    'Badge-Text',  $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('footnote', 'Fußnote',     $d['footnote'] ?? '');
  ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Preiszeilen</p>
  <div id="priceTableRepeater">
  <?php foreach ($rows as $i => $item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">#<?= $i+1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group"><label>Bezeichnung</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($item['title'] ?? $item['label'] ?? '') ?>"></div>
      <div class="form-group"><label>Preis</label>
        <input type="text" name="items[<?= $i ?>][price]" value="<?= htmlspecialchars($item['price'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Notiz</label>
      <input type="text" name="items[<?= $i ?>][note]" value="<?= htmlspecialchars($item['note'] ?? '') ?>" placeholder="z.B. netto, inkl. Wien"></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Zeile hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
