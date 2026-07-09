<?php
// includes/section-forms/partners.php
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
  <?php sf_text('label', 'Badge-Text', $d['label'] ?? ''); ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">Partner-Logos</p>
  <div id="partnersRepeater">
  <?php foreach ($rows as $i => $item): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">#<?= $i+1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label>
        <input type="text" name="items[<?= $i ?>][name]" value="<?= htmlspecialchars($item['name'] ?? '') ?>" placeholder="z.B. ORF"></div>
      <div class="form-group"><label>Logo URL</label>
        <?php $uid = 'pt_logo_' . $i . '_' . bin2hex(random_bytes(3)); ?>
        <div class="image-field-wrap">
          <input id="<?= $uid ?>" type="url" name="items[<?= $i ?>][logo]" value="<?= htmlspecialchars($item['logo'] ?? '') ?>" placeholder="https://…" class="image-field-input">
          <button type="button" class="btn btn-secondary btn-sm media-picker-btn" data-target="<?= $uid ?>">Bild wählen</button>
        </div>
        <div class="image-field-preview" id="<?= $uid ?>_preview">
        <?php if (!empty($item['logo'])): ?><img src="<?= htmlspecialchars($item['logo']) ?>" alt="" style="max-width:120px;max-height:40px;object-fit:contain"><button type="button" class="image-field-clear" data-target="<?= $uid ?>" title="Bild entfernen">&times;</button><?php endif; ?>
        </div></div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Logo hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
