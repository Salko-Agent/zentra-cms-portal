<?php
// includes/section-forms/faq.php
// FAQ accordion: label, headline, subtext + repeater (question / answer)
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
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">FAQ-Einträge</p>
  <div id="faqRepeater">
  <?php
  if (empty($rows)) $rows = [['question' => '', 'answer' => '']];
  foreach ($rows as $i => $faq): ?>
  <div class="repeater-row">
    <div class="repeater-row-header">Frage <?= $i + 1 ?><button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-group">
      <label>Frage</label>
      <input type="text" name="items[<?= $i ?>][question]" value="<?= htmlspecialchars($faq['question'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Antwort</label>
      <textarea name="items[<?= $i ?>][answer]"><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Frage hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
