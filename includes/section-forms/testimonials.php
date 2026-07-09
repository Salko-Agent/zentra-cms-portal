<?php
// includes/section-forms/testimonials.php
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
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Bewertungen</p>
  <div id="testiRepeater">
  <?php foreach ($rows as $i => $t): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($t['name'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-group"><label>Zitat</label>
      <textarea name="items[<?= $i ?>][text]"><?= htmlspecialchars($t['text'] ?? '') ?></textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label>
        <input type="text" name="items[<?= $i ?>][name]" value="<?= htmlspecialchars($t['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Initialen (2–3 Zeichen)</label>
        <input type="text" name="items[<?= $i ?>][initials]" value="<?= htmlspecialchars($t['initials'] ?? '') ?>" maxlength="3" placeholder="z.B. PK"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Sterne (1–5)</label>
        <input type="number" name="items[<?= $i ?>][rating]" value="<?= (int)($t['rating'] ?? 5) ?>" min="1" max="5"></div>
      <div class="form-group"><label>Datum</label>
        <input type="text" name="items[<?= $i ?>][date]" value="<?= htmlspecialchars($t['date'] ?? '') ?>" placeholder="z.B. September 2025"></div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Bewertung hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
