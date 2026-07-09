<?php
// includes/section-forms/stats_strip.php
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows (each row has item_json)
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
  <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:14px">Statistik-Zahlen (max. 5)</p>
  <div id="statsRepeater">
  <?php foreach ($rows as $i => $item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">
      #<?= $i+1 ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Zahl</label>
        <input type="text" name="items[<?= $i ?>][number]" value="<?= htmlspecialchars($item['number'] ?? '') ?>" placeholder="z.B. 15+">
      </div>
      <div class="form-group">
        <label>Bezeichnung</label>
        <input type="text" name="items[<?= $i ?>][label]" value="<?= htmlspecialchars($item['label'] ?? '') ?>" placeholder="z.B. Jahre Erfahrung">
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add" data-container="statsRepeater">+ Zeile hinzufügen</button>
  <div style="margin-top:16px">
    <button type="submit" class="btn btn-primary btn-sm">Speichern</button>
  </div>
</form>
