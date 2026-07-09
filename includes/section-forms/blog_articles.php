<?php
// includes/section-forms/blog_articles.php
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
  <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">Artikel</p>
  <div id="blogRepeater">
  <?php foreach ($rows as $i => $a): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($a['title'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 80px"><label>Emoji</label>
        <input type="text" name="items[<?= $i ?>][emoji]" value="<?= htmlspecialchars($a['emoji'] ?? '') ?>" maxlength="4" style="text-align:center"></div>
      <div class="form-group"><label>Datum</label>
        <input type="text" name="items[<?= $i ?>][date]" value="<?= htmlspecialchars($a['date'] ?? '') ?>" placeholder="TT/MM/JJJJ"></div>
    </div>
    <div class="form-group"><label>Titel</label>
      <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($a['title'] ?? '') ?>"></div>
    <div class="form-group"><label>Teaser</label>
      <textarea name="items[<?= $i ?>][excerpt]"><?= htmlspecialchars($a['excerpt'] ?? '') ?></textarea></div>
    <div class="form-group"><label>URL</label>
      <input type="url" name="items[<?= $i ?>][url]" value="<?= htmlspecialchars($a['url'] ?? '') ?>" placeholder="https://…"></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Artikel hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
