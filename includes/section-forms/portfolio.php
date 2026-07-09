<?php
// includes/section-forms/portfolio.php
// Portfolio grid: label, headline, subtext + repeater (title/category/description/image/url/year)
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
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Portfolio-Projekte</p>
  <div id="portfolioRepeater">
  <?php
  if (empty($rows)) $rows = [['title' => '', 'category' => '', 'year' => '', 'description' => '', 'image' => '', 'url' => '']];
  foreach ($rows as $i => $proj): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">Projekt <?= $i + 1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-group">
      <label>Projekttitel</label>
      <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($proj['title'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Kategorie / Tag</label>
        <input type="text" name="items[<?= $i ?>][category]" value="<?= htmlspecialchars($proj['category'] ?? '') ?>" placeholder="z.B. Webdesign">
      </div>
      <div class="form-group">
        <label>Jahr</label>
        <input type="text" name="items[<?= $i ?>][year]" value="<?= htmlspecialchars($proj['year'] ?? '') ?>" placeholder="z.B. 2024">
      </div>
    </div>
    <div class="form-group">
      <label>Kurzbeschreibung</label>
      <textarea name="items[<?= $i ?>][description]"><?= htmlspecialchars($proj['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Bild-URL</label>
      <?php $uid = 'pf_img_' . $i . '_' . bin2hex(random_bytes(3)); ?>
      <div class="image-field-wrap">
        <input id="<?= $uid ?>" type="url" name="items[<?= $i ?>][image]" value="<?= htmlspecialchars($proj['image'] ?? '') ?>" placeholder="https://…" class="image-field-input">
        <button type="button" class="btn btn-secondary btn-sm media-picker-btn" data-target="<?= $uid ?>">Bild wählen</button>
      </div>
      <div class="image-field-preview" id="<?= $uid ?>_preview">
      <?php if (!empty($proj['image'])): ?>
        <img src="<?= htmlspecialchars($proj['image']) ?>" alt="">
        <button type="button" class="image-field-clear" data-target="<?= $uid ?>" title="Bild entfernen">&times;</button>
      <?php endif; ?>
      </div>
    </div>
    <div class="form-group">
      <label>Projekt-URL (optional)</label>
      <input type="url" name="items[<?= $i ?>][url]" value="<?= htmlspecialchars($proj['url'] ?? '') ?>" placeholder="https://…">
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Projekt hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
