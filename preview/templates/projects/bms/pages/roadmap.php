<?php
// preview/templates/projects/bms/pages/roadmap.php
$seo    = $c['pages']['roadmap']['seo']      ?? [];
$secs   = $c['pages']['roadmap']['sections'] ?? [];

$hero_d = $secs['hero']['data']     ?? [];
$tl_d   = $secs['timeline']['data'] ?? [];
$tl_i   = $tl_d['items']            ?? [];
$cta_d  = $secs['cta']['data']      ?? [];

echo bms_render_head(h($seo['title'] ?? 'Roadmap | BMS Digital Solutions Wien'), $seo);
echo bms_render_navbar($c);
?>

<!-- HERO -->
<section class="bms-hero" style="padding:100px 0 64px">
  <div class="container">
    <?php if (!empty($hero_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:16px"><?= h($hero_d['label']) ?></div><?php endif; ?>
    <h1><?= nl2br(h($hero_d['headline'] ?? '')) ?></h1>
    <p><?= h($hero_d['subtext'] ?? '') ?></p>
  </div>
</section>

<!-- TIMELINE -->
<?php if (!empty($tl_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:56px">
      <?php if (!empty($tl_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($tl_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($tl_d['headline'])): ?><h2><?= h($tl_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($tl_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($tl_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div class="bms-timeline">
      <?php foreach ($tl_i as $entry): ?>
      <div class="bms-timeline-item">
        <div class="bms-timeline-dot">
          <?php if (!empty($entry['icon'])): ?>
            <?= h($entry['icon']) ?>
          <?php else: ?>
            <span style="width:10px;height:10px;border-radius:50%;background:var(--bms-accent);display:block"></span>
          <?php endif; ?>
        </div>
        <div class="bms-timeline-body">
          <?php if (!empty($entry['date'])): ?><div style="font-size:.78rem;font-weight:700;color:var(--bms-accent);letter-spacing:.05em;text-transform:uppercase;margin-bottom:4px"><?= h($entry['date']) ?></div><?php endif; ?>
          <h3 style="font-size:1.05rem;margin-bottom:6px"><?= h($entry['title'] ?? '') ?></h3>
          <?php if (!empty($entry['description'])): ?><p style="color:var(--bms-muted);font-size:.875rem"><?= h($entry['description']) ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<?php if (!empty($cta_d['headline'])): ?>
<section class="section-pad">
  <div class="container">
    <div class="bms-cta">
      <h2><?= h($cta_d['headline']) ?></h2>
      <?php if (!empty($cta_d['subtext'])): ?><p><?= h($cta_d['subtext']) ?></p><?php endif; ?>
      <div class="bms-cta-actions">
        <?php if (!empty($cta_d['btn_primary'])): ?><a href="<?= h($cta_d['btn_primary_url'] ?? '#') ?>" class="btn btn-primary"><?= h($cta_d['btn_primary']) ?></a><?php endif; ?>
        <?php if (!empty($cta_d['btn_secondary'])): ?><a href="<?= h($cta_d['btn_secondary_url'] ?? '#') ?>" class="btn btn-outline"><?= h($cta_d['btn_secondary']) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php echo bms_render_footer($c); ?>
