<?php
// includes/section-forms/timeline.php
// Timeline / Meilensteine: label, headline, subtext + repeater (date/year, title, description, icon)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

$rows = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $rows[] = $obj;
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
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Timeline-Einträge</p>
  <div id="timelineRepeater">
  <?php
  if (empty($rows)) $rows = [['date' => '', 'icon' => '', 'title' => '', 'description' => '']];
  foreach ($rows as $i => $entry): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">Eintrag <?= $i + 1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 100px">
        <label>Jahr / Datum</label>
        <input type="text" name="items[<?= $i ?>][date]" value="<?= htmlspecialchars($entry['date'] ?? $entry['year'] ?? '') ?>" placeholder="z.B. 2021">
      </div>
      <div class="form-group" style="flex:0 0 60px">
        <label>Icon</label>
        <input type="text" name="items[<?= $i ?>][icon]" value="<?= htmlspecialchars($entry['icon'] ?? '') ?>" placeholder="🚀">
      </div>
      <div class="form-group">
        <label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($entry['title'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Beschreibung</label>
      <textarea name="items[<?= $i ?>][description]"><?= htmlspecialchars($entry['description'] ?? $entry['text'] ?? '') ?></textarea>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Eintrag hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
