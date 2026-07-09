<?php
/**
 * Preview partial: Portfolio grid section
 * Variables expected: $d (section data array), $items (array of project rows)
 * Usage in page template:
 *   $d     = $c['pages']['slug']['sections']['portfolio']['data'] ?? [];
 *   $items = $d['items'] ?? [];
 *   include __DIR__ . '/../../templates/sections/portfolio.php';
 */
$label    = h($d['label']    ?? 'Portfolio');
$headline = $d['headline']   ?? '';
$subtext  = h($d['subtext']  ?? '');
?>
<section class="section-pad">
  <div class="container">
    <div class="text-center reveal" style="margin-bottom:48px">
      <?php if ($label): ?><span class="section-label"><?= $label ?></span><?php endif; ?>
      <?php if ($headline): ?><h2><?= nl2br(h($headline)) ?></h2><?php endif; ?>
      <?php if ($subtext): ?><p style="color:var(--text-secondary);margin-top:12px;max-width:560px;margin-inline:auto"><?= $subtext ?></p><?php endif; ?>
    </div>
    <?php if (!empty($items)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px">
      <?php foreach ($items as $proj):
        $title = h($proj['title'] ?? '');
        if (!$title) continue;
        $cat   = h($proj['category']    ?? '');
        $year  = h($proj['year']        ?? '');
        $desc  = h($proj['description'] ?? '');
        $img   = h($proj['image']       ?? '');
        $url   = h($proj['url']         ?? '');
      ?>
      <div class="card reveal" style="padding:0;overflow:hidden">
        <?php if ($img): ?>
        <img src="<?= $img ?>" alt="<?= $title ?>" loading="lazy"
             style="width:100%;aspect-ratio:16/9;object-fit:cover;display:block">
        <?php endif; ?>
        <div style="padding:24px">
          <?php if ($cat || $year): ?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
            <?php if ($cat): ?><span class="badge badge-gold"><?= $cat ?></span><?php endif; ?>
            <?php if ($year): ?><span style="font-size:.75rem;color:var(--text-muted);align-self:center"><?= $year ?></span><?php endif; ?>
          </div>
          <?php endif; ?>
          <h3 style="margin-bottom:8px;font-size:1.15rem"><?= $title ?></h3>
          <?php if ($desc): ?><p style="color:var(--text-secondary);font-size:.88rem;margin-bottom:16px"><?= $desc ?></p><?php endif; ?>
          <?php if ($url): ?><a href="<?= $url ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm" style="font-size:.82rem">Projekt ansehen →</a><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
