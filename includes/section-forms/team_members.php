<?php
// includes/section-forms/team_members.php
// Used for: physio/team (name, phone, photo, initials)
//           team/trainers (name, title, credentials, photo, bio, specializations)
require_once __DIR__ . '/_helpers.php';
$d = $sec_data;

// Decode items from DB rows
$rows = [];
foreach ($sec_items as $it) {
    $obj = json_decode($it['item_json'] ?? '{}', true);
    if ($obj) $rows[] = $obj;
}
// Fallback: members stored as field
if (empty($rows) && !empty($d['members'])) {
    $arr = is_string($d['members']) ? json_decode($d['members'], true) : $d['members'];
    if (is_array($arr)) $rows = $arr;
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
  sf_textarea('subtext','Untertext',     $d['subtext']  ?? '');
  ?>
  <p style="font-size:.78rem;color:var(--text-muted);margin:14px 0 10px">Teammitglieder</p>
  <div id="teamRepeater">
  <?php foreach ($rows as $i => $m): ?>
  <div class="repeater-row">
    <div class="repeater-row-header"><?= htmlspecialchars($m['name'] ?? '#'.($i+1)) ?>
      <button type="button" class="btn btn-danger btn-sm repeater-remove">✕</button></div>
    <div class="form-row">
      <div class="form-group"><label>Name</label>
        <input type="text" name="items[<?= $i ?>][name]" value="<?= htmlspecialchars($m['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Titel / Rolle</label>
        <input type="text" name="items[<?= $i ?>][title]" value="<?= htmlspecialchars($m['title'] ?? $m['role'] ?? '') ?>" placeholder="z.B. Personal Trainer"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Telefon</label>
        <input type="text" name="items[<?= $i ?>][phone]" value="<?= htmlspecialchars($m['phone'] ?? '') ?>" placeholder="+43…"></div>
      <div class="form-group"><label>Initialen</label>
        <input type="text" name="items[<?= $i ?>][initials]" value="<?= htmlspecialchars($m['initials'] ?? '') ?>" maxlength="3"></div>
    </div>
    <div class="form-group"><label>Foto URL</label>
      <?php $uid = 'tm_photo_' . $i . '_' . bin2hex(random_bytes(3)); ?>
      <div class="image-field-wrap">
        <input id="<?= $uid ?>" type="url" name="items[<?= $i ?>][photo]" value="<?= htmlspecialchars($m['photo'] ?? '') ?>" class="image-field-input">
        <button type="button" class="btn btn-secondary btn-sm media-picker-btn" data-target="<?= $uid ?>">Bild wählen</button>
      </div>
      <div class="image-field-preview" id="<?= $uid ?>_preview">
      <?php if (!empty($m['photo'])): ?><img src="<?= htmlspecialchars($m['photo']) ?>" alt="" style="max-width:80px;max-height:80px;border-radius:50%;object-fit:cover"><button type="button" class="image-field-clear" data-target="<?= $uid ?>" title="Bild entfernen">&times;</button><?php endif; ?>
      </div></div>
    <div class="form-group"><label>Qualifikationen (kommasepariert)</label>
      <input type="text" name="items[<?= $i ?>][credentials]" value="<?= htmlspecialchars($m['credentials'] ?? '') ?>" placeholder="Sportwissenschaftler · Fitnesstrainer"></div>
    <div class="form-group"><label>Bio</label>
      <textarea name="items[<?= $i ?>][bio]"><?= htmlspecialchars($m['bio'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Spezialisierungen (Zeilenumbruch-getrennt)</label>
      <textarea name="items[<?= $i ?>][specializations]"><?php
        $sp = $m['specializations'] ?? '';
        if (is_array($sp)) echo htmlspecialchars(implode("\n", $sp));
        else echo htmlspecialchars($sp);
      ?></textarea></div>
  </div>
  <?php endforeach; ?>
  </div>
  <button type="button" class="repeater-add">+ Person hinzufügen</button>
  <div style="margin-top:16px"><button type="submit" class="btn btn-primary btn-sm">Speichern</button></div>
</form>
