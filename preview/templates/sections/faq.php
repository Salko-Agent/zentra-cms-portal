<?php
/**
 * Preview partial: FAQ accordion section
 * Variables expected: $d (section data array), $items (array of FAQ rows)
 * Usage in page template:
 *   $d     = $c['pages']['slug']['sections']['faq']['data'] ?? [];
 *   $items = $d['items'] ?? [];
 *   include __DIR__ . '/../../templates/sections/faq.php';
 */
$label    = h($d['label']    ?? 'FAQ');
$headline = $d['headline']   ?? '';
$subtext  = h($d['subtext']  ?? '');
?>
<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <?php if ($label): ?><span class="section-label"><?= $label ?></span><?php endif; ?>
      <?php if ($headline): ?><h2><?= nl2br(h($headline)) ?></h2><?php endif; ?>
      <?php if ($subtext): ?><p style="color:var(--text-secondary);margin-top:12px;max-width:560px;margin-inline:auto"><?= $subtext ?></p><?php endif; ?>
    </div>
    <?php if (!empty($items)): ?>
    <div style="max-width:720px;margin-inline:auto" class="reveal">
      <?php foreach ($items as $i => $faq):
        $q = h($faq['question'] ?? '');
        $a = h($faq['answer']   ?? '');
        if (!$q) continue;
        $id = 'faq_' . $i;
      ?>
      <div style="border:1px solid var(--border-light);border-radius:var(--r-md);margin-bottom:8px;overflow:hidden">
        <button
          onclick="var b=this.nextElementSibling;var open=b.style.display==='block';document.querySelectorAll('.faq-body').forEach(function(x){x.style.display='none';x.previousElementSibling.querySelector('.faq-icon').textContent='+'});if(!open){b.style.display='block';this.querySelector('.faq-icon').textContent='−'}"
          style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:18px 20px;background:var(--white);border:none;cursor:pointer;text-align:left;font-size:1rem;font-weight:600;font-family:inherit;color:inherit;gap:12px"
          aria-expanded="false"
          aria-controls="<?= $id ?>">
          <?= $q ?>
          <span class="faq-icon" style="font-size:1.4rem;font-weight:300;color:var(--gold);flex-shrink:0">+</span>
        </button>
        <div class="faq-body" id="<?= $id ?>" style="display:none;padding:0 20px 18px;background:var(--white);color:var(--text-secondary);line-height:1.65;font-size:.95rem">
          <?= nl2br($a) ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
