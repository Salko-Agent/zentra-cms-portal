<?php
// includes/section-forms/steps.php
// Used for: numbered steps (number/title/text) and plain text steps (text only)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows
$rows = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $rows[] = $obj;
}
// Fallback: steps may be stored under various field keys (depends on sync source)
if (empty($rows)) {
    foreach (['steps', 'ablauf_steps', 'items'] as $_fk) {
        if (!empty($d[$_fk])) {
            $raw = is_string($d[$_fk]) ? (json_decode($d[$_fk], true) ?: []) : $d[$_fk];
            if (is_array($raw)) {
                $rows = array_map(fn($s) => is_string($s) ? ['text' => $s] : $s, $raw);
                break;
            }
        }
    }
}

// Detect if items have title/number fields
$first = $rows[0] ?? [];
$has_title = isset($first['title']) || isset($first['number']);
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_text('label',    'Badge-Text',  $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',   $d['subtext']  ?? '');
  ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Schritte</p>
  <div id="stepsRepeater">
  <?php foreach ($rows as $i => $step): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">Schritt <?= $i+1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <?php if ($has_title || isset($step['title']) || isset($step['number'])): ?>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 70px"><label>Nr.</label>
        <input type="text" name="items[<?= $i ?>][number]" value="<?= htmlspecialchars($step['number'] ?? (string)($i+1)) ?>"></div>
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($step['title'] ?? '') ?>"></div>
    </div>
    <?php endif; ?>
    <div class="form-group"><label>Text / Beschreibung</label>
      <textarea name="items[<?= $i ?>][text]"><?= htmlspecialchars($step['text'] ?? (is_string($step) ? $step : '')) ?></textarea></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Schritt hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
