<?php
// preview/templates/projects/bms/pages/impressum.php
$seo  = $c['pages']['impressum']['seo']      ?? [];
$secs = $c['pages']['impressum']['sections'] ?? [];
$d    = $secs['legal']['data'] ?? [];

echo bms_render_head(h($seo['title'] ?? 'Impressum | BMS Digital Solutions'), $seo);
echo bms_render_navbar($c);
?>
<section class="section-pad" style="padding-top:100px">
  <div class="container" style="max-width:720px">
    <div class="badge badge-accent" style="margin-bottom:16px">Rechtliches</div>
    <h1 style="margin-bottom:32px">Impressum</h1>
    <div class="card" style="line-height:1.8">
      <p><strong><?= h($d['impressum_company'] ?? 'BMS Digital Solutions') ?></strong><br>
         <?= h($d['impressum_name'] ?? 'Salih Batanovic') ?><br>
         <?= h($d['impressum_address'] ?? 'Wien, Österreich') ?></p>
      <p style="margin-top:16px">
        <strong>E-Mail:</strong> <a href="mailto:<?= h($d['impressum_email'] ?? '') ?>"><?= h($d['impressum_email'] ?? '') ?></a><br>
        <strong>Tel:</strong> <?= h($d['impressum_phone'] ?? '') ?><br>
        <?php if (!empty($d['impressum_uid'])): ?><strong>UID:</strong> <?= h($d['impressum_uid']) ?><?php endif; ?>
      </p>
    </div>
  </div>
</section>
<?php echo bms_render_footer($c); ?>
