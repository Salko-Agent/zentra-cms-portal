<?php
// preview/templates/projects/bms/pages/ueber-uns.php
$seo      = $c['pages']['ueber-uns']['seo']      ?? [];
$secs     = $c['pages']['ueber-uns']['sections'] ?? [];

$hero_d   = $secs['hero']['data']       ?? [];
$about_d  = $secs['about']['data']      ?? [];
$about_i  = $about_d['items']           ?? [];
$tech_d   = $secs['tech_stack']['data'] ?? [];
$tech_i   = $tech_d['items']            ?? [];
$val_d    = $secs['values']['data']     ?? [];
$val_i    = $val_d['items']             ?? [];
$cta_d    = $secs['cta']['data']        ?? [];

echo bms_render_head(h($seo['title'] ?? 'Über uns | BMS Digital Solutions Wien'), $seo);
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

<!-- ABOUT -->
<?php if (!empty($about_d['headline']) || !empty($about_d['text'])): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:48px;align-items:start">
      <div>
        <?php if (!empty($about_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($about_d['label']) ?></div><?php endif; ?>
        <?php if (!empty($about_d['headline'])): ?><h2 style="font-size:clamp(1.4rem,3vw,2rem)"><?= nl2br(h($about_d['headline'])) ?></h2><?php endif; ?>
        <?php if (!empty($about_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:10px;font-size:.9rem"><?= h($about_d['subtext']) ?></p><?php endif; ?>
        <?php if (!empty($about_d['cta'])): ?><a href="<?= h($about_d['cta_url'] ?? '#') ?>" class="btn btn-primary" style="margin-top:20px"><?= h($about_d['cta']) ?></a><?php endif; ?>
      </div>
      <div>
        <?php if (!empty($about_d['text'])): ?>
        <div style="padding:28px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:var(--bms-r)">
          <p style="color:var(--bms-text);line-height:1.8;font-size:1rem"><?= nl2br(h($about_d['text'])) ?></p>
        </div>
        <?php endif; ?>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap">
          <div style="padding:10px 16px;background:var(--bms-card);border:1px solid var(--bms-border);border-radius:8px;font-size:.82rem;color:var(--bms-muted)">📍 Herrengasse 6, 3002 Purkersdorf</div>
          <div style="padding:10px 16px;background:var(--bms-card);border:1px solid var(--bms-border);border-radius:8px;font-size:.82rem;color:var(--bms-muted)">🏢 BMS Digital Solutions</div>
        </div>
      </div>
    </div>
  </div>
</section>
<style>@media(max-width:640px){.about-grid{grid-template-columns:1fr!important}}</style>
<?php endif; ?>

<!-- TECH STACK -->
<?php if (!empty($tech_i)): ?>
<section class="section-pad">
  <div class="container">
    <div class="text-center" style="margin-bottom:48px">
      <?php if (!empty($tech_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($tech_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($tech_d['headline'])): ?><h2><?= h($tech_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($tech_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($tech_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
      <?php foreach ($tech_i as $tech): ?>
      <div class="card" style="display:flex;gap:14px;align-items:flex-start">
        <div style="font-size:1.6rem;flex-shrink:0"><?= h($tech['icon'] ?? '') ?></div>
        <div>
          <h3 style="font-size:1rem;margin-bottom:6px"><?= h($tech['title'] ?? '') ?></h3>
          <p style="color:var(--bms-muted);font-size:.82rem"><?= h($tech['text'] ?? '') ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- VALUES -->
<?php if (!empty($val_i)): ?>
<section class="section-pad" style="background:var(--bms-dark)">
  <div class="container">
    <div class="text-center" style="margin-bottom:40px">
      <?php if (!empty($val_d['label'])): ?><div class="badge badge-accent" style="margin-bottom:12px"><?= h($val_d['label']) ?></div><?php endif; ?>
      <?php if (!empty($val_d['headline'])): ?><h2><?= h($val_d['headline']) ?></h2><?php endif; ?>
      <?php if (!empty($val_d['subtext'])): ?><p style="color:var(--bms-muted);margin-top:12px;max-width:520px;margin-inline:auto"><?= h($val_d['subtext']) ?></p><?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;max-width:900px;margin-inline:auto">
      <?php foreach ($val_i as $val): ?>
      <div class="card" style="text-align:center">
        <div style="font-size:2.2rem;margin-bottom:12px"><?= h($val['icon'] ?? '') ?></div>
        <h3 style="margin-bottom:8px"><?= h($val['title'] ?? '') ?></h3>
        <p style="color:var(--bms-muted);font-size:.875rem"><?= h($val['text'] ?? '') ?></p>
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
