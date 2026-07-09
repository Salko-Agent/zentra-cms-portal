<?php
/**
 * Preview partial: Timeline / Meilensteine section
 * Variables expected: $d (section data array), $items (array of timeline entries)
 * Usage in page template:
 *   $d     = $c['pages']['slug']['sections']['timeline']['data'] ?? [];
 *   $items = $d['items'] ?? [];
 *   include __DIR__ . '/../../templates/sections/timeline.php';
 */
$label    = h($d['label']    ?? 'Timeline');
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
    <div style="max-width:680px;margin-inline:auto;position:relative">
      <!-- Vertical line -->
      <div style="position:absolute;left:28px;top:0;bottom:0;width:2px;background:var(--border-light)"></div>
      <?php foreach ($items as $entry):
        $date  = h($entry['date']        ?? $entry['year']        ?? '');
        $icon  = $entry['icon']          ?? '●';
        $title = h($entry['title']       ?? '');
        $desc  = h($entry['description'] ?? $entry['text']        ?? '');
        if (!$title && !$desc) continue;
      ?>
      <div class="reveal" style="display:flex;gap:24px;margin-bottom:36px;align-items:flex-start;position:relative">
        <div style="flex-shrink:0;width:56px;height:56px;border-radius:50%;background:var(--white);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:1.4rem;z-index:1;position:relative">
          <?= htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
        <div style="padding-top:8px;flex:1">
          <?php if ($date): ?><div style="font-size:.78rem;font-weight:700;color:var(--gold);letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px"><?= $date ?></div><?php endif; ?>
          <?php if ($title): ?><h3 style="font-size:1.05rem;margin-bottom:6px"><?= $title ?></h3><?php endif; ?>
          <?php if ($desc): ?><p style="color:var(--text-secondary);font-size:.9rem;line-height:1.6;margin:0"><?= nl2br($desc) ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
