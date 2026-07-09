<?php
// includes/section-forms/list_items.php
// Generic: benefits (icon/title/text), services (title/short/description/image/url/price),
//          angebote (number/title/text), etc.
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

// Detect item type from first row to show appropriate fields
$first = $rows[0] ?? [];
$has_service_fields = isset($first['description']) || isset($first['short']) || isset($first['url']);
$has_number = isset($first['number']);
?>
<form class="section-form" method="POST">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="section_id" value="<?= (int)$sec['id'] ?>">
  <?php
  sf_text('label',    'Badge-Text', $d['label']    ?? '');
  sf_textarea('headline', 'Überschrift', $d['headline'] ?? '');
  sf_textarea('subtext',  'Untertext',   $d['subtext']  ?? '');
  sf_image('image',       'Bild URL', $d['image'] ?? '');
  ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Einträge</p>
  <div id="listRepeater">
  <?php foreach ($rows as $i => $item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($item['title'] ?? '#'.($i+1)) ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <?php if ($has_number || isset($item['number'])): ?>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 70px"><label>Nr.</label>
        <input type="text" name="items[<?= $i ?>][number]" value="<?= htmlspecialchars($item['number'] ?? '') ?>" placeholder="1"></div>
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($item['title'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Text</label>
      <textarea name="items[<?= $i ?>][text]"><?= htmlspecialchars($item['text'] ?? '') ?></textarea></div>
    <?php elseif ($has_service_fields || isset($item['description'])): ?>
    <div class="form-row">
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($item['title'] ?? '') ?>"></div>
      <div class="form-group"><label>Kurztext</label>
        <input type="text" name="items[<?= $i ?>][short]" value="<?= htmlspecialchars($item['short'] ?? '') ?>" placeholder="z.B. 1:1 Training"></div>
    </div>
    <div class="form-group"><label>Beschreibung</label>
      <textarea name="items[<?= $i ?>][description]"><?= htmlspecialchars($item['description'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Bild URL</label>
      <?php $uid = 'li_img_' . $i . '_' . bin2hex(random_bytes(3)); ?>
      <div class="image-field-wrap">
        <input id="<?= $uid ?>" type="url" name="items[<?= $i ?>][image]" value="<?= htmlspecialchars($item['image'] ?? '') ?>" placeholder="https://…" class="image-field-input">
        <button type="button" class="btn btn-secondary btn-sm media-picker-btn" data-target="<?= $uid ?>">Bild wählen</button>
      </div>
      <div class="image-field-preview" id="<?= $uid ?>_preview">
      <?php if (!empty($item['image'])): ?><img src="<?= htmlspecialchars($item['image']) ?>" alt=""><button type="button" class="image-field-clear" data-target="<?= $uid ?>" title="Bild entfernen">&times;</button><?php endif; ?>
      </div></div>
    <div class="form-row">
      <div class="form-group"><label>Preis</label>
        <input type="text" name="items[<?= $i ?>][price]" value="<?= htmlspecialchars($item['price'] ?? '') ?>" placeholder="ab 69 €"></div>
      <div class="form-group"><label>Einheit</label>
        <input type="text" name="items[<?= $i ?>][price_unit]" value="<?= htmlspecialchars($item['price_unit'] ?? '') ?>" placeholder="/ Einheit"></div>
    </div>
    <div class="form-group"><label>URL (Verlinkung)</label>
      <input type="text" name="items[<?= $i ?>][url]" value="<?= htmlspecialchars($item['url'] ?? '') ?>" placeholder="/personal-training"></div>
    <?php else: ?>
    <div class="form-row">
      <div class="form-group" style="flex:0 0 70px"><label>Icon</label>
        <input type="text" name="items[<?= $i ?>][icon]" value="<?= htmlspecialchars($item['icon'] ?? '') ?>" style="text-align:center"></div>
      <div class="form-group"><label>Titel</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($item['title'] ?? '') ?>"></div>
    </div>
    <div class="form-group"><label>Text</label>
      <textarea name="items[<?= $i ?>][text]"><?= htmlspecialchars($item['text'] ?? '') ?></textarea></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Eintrag hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
