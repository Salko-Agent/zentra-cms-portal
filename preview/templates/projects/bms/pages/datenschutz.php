<?php
// preview/templates/projects/bms/pages/datenschutz.php
$seo = $c['pages']['datenschutz']['seo'] ?? [];

echo bms_render_head(h($seo['title'] ?? 'Datenschutz | BMS Digital Solutions'), $seo);
echo bms_render_navbar($c);
?>
<section class="section-pad" style="padding-top:100px">
  <div class="container" style="max-width:720px">
    <div class="badge badge-accent" style="margin-bottom:16px">Rechtliches</div>
    <h1 style="margin-bottom:32px">Datenschutzerklärung</h1>
    <div class="card" style="line-height:1.8;color:var(--bms-muted)">
      <p>Inhalt wird über das CMS verwaltet. Bitte den vollständigen Datenschutztext hier einfügen.</p>
    </div>
  </div>
</section>
<?php echo bms_render_footer($c); ?>
