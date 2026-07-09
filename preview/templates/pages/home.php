<?php
// preview/templates/pages/home.php
// Variables available: $c (full content array), $slug

$seo     = $c['pages']['home']['seo'] ?? [];
$hero    = $c['pages']['home']['sections']['hero']['data'] ?? [];
$stats   = $c['pages']['home']['sections']['stats']['data']['items'] ?? [];
$partners= $c['pages']['home']['sections']['partners']['data'] ?? [];
$why     = $c['pages']['home']['sections']['why']['data'] ?? [];
$services= $c['pages']['home']['sections']['services']['data']['items'] ?? [];
$trainer = $c['pages']['home']['sections']['trainer']['data'] ?? [];
$benefits= $c['pages']['home']['sections']['benefits']['data'] ?? [];
$benfits_items = $benefits['items'] ?? [];
$testimonials  = $c['pages']['home']['sections']['testimonials']['data']['items'] ?? [];
$contact_d     = $c['pages']['kontakt']['sections']['contact']['data'] ?? [];

echo render_head(h($seo['title'] ?? 'Personal Trainer Wien | FlexFit'), $seo);
echo render_navbar($c);

// Stats strip
$stat_html = '';
foreach ($stats as $i => $s) {
    $delay = $i + 1;
    $stat_html .= '<div class="stat-item reveal reveal-delay-' . $delay . '">'
        . '<div class="stat-number">' . h($s['number'] ?? '') . '</div>'
        . '<div class="stat-label">'  . h($s['label']  ?? '') . '</div>'
        . '</div>';
}

// Partners
$partner_html = '';
foreach ($partners['items'] ?? [] as $p) {
    $partner_html .= '<img src="' . h($p['logo'] ?? '') . '" alt="' . h($p['name'] ?? '') . '" class="partner-logo" height="28" loading="lazy">';
}

// Why features
$why_features_html = '';
foreach ($why['items'] ?? [] as $i => $f) {
    $delay = $i + 1;
    $why_features_html .= <<<HTML
<div class="why-feature reveal reveal-delay-{$delay}">
  <div class="why-feature-icon">{$f['icon']}</div>
  <div>
    <div class="why-feature-title">{$f['title']}</div>
    <div class="why-feature-text">{$f['text']}</div>
  </div>
</div>
HTML;
}
$goal_badges = '';
$goals_raw = $why['goals'] ?? [];
if (is_string($goals_raw)) {
    $decoded = json_decode($goals_raw, true);
    $goals_raw = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $goals_raw))));
}
foreach ($goals_raw as $g) {
    $goal_badges .= '<span class="badge badge-gold">✓ ' . h($g) . '</span>';
}

// Services
$service_html = '';
foreach ($services as $i => $s) {
    $delay = $i + 1;
    $service_html .= <<<HTML
<article class="service-card reveal reveal-delay-{$delay}">
  <div class="service-card-image">
    <img src="{$s['image']}" alt="{$s['title']}" loading="lazy">
    <div class="service-card-overlay"></div>
    <div class="service-card-badge"><span class="badge badge-dark">{$s['short']}</span></div>
  </div>
  <div class="service-card-body">
    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;margin-bottom:8px">
      <h3 class="service-card-title">{$s['title']}</h3>
      <span style="color:var(--gold);font-size:.85rem;font-weight:700">{$s['price']}</span>
    </div>
    <p class="service-card-text">{$s['description']}</p>
    <a href="{$s['url']}" class="service-card-link">Mehr erfahren →</a>
  </div>
</article>
HTML;
}

// Trainer specs (stored as section_items)
$trainer_specs_html = '';
foreach ($trainer['items'] ?? [] as $sp) {
    if (is_string($sp)) $sp = json_decode($sp, true) ?: [];
    $trainer_specs_html .= <<<HTML
<div class="trainer-spec">
  <span class="trainer-spec-icon">{$sp['icon']}</span>
  <div>
    <div class="trainer-spec-label">{$sp['label']}</div>
    <div class="trainer-spec-value">{$sp['value']}</div>
  </div>
</div>
HTML;
}
// (specs already rendered above from items)

$specializations_raw = $trainer['specializations'] ?? [];
if (is_string($specializations_raw)) {
    $decoded = json_decode($specializations_raw, true);
    $specializations_raw = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $specializations_raw))));
}
$trainer_specializations_html = '';
foreach ($specializations_raw as $sp) {
    $trainer_specializations_html .= '<div style="display:flex;align-items:center;gap:10px;font-size:.88rem;color:rgba(255,255,255,.75)"><span style="color:var(--gold)">✓</span> ' . h($sp) . '</div>';
}

// Benefits
$benefit_html = '';
foreach ($benfits_items as $i => $b) {
    $delay = ($i % 3) + 1;
    $benefit_html .= <<<HTML
<div class="benefit-card reveal reveal-delay-{$delay}">
  <div class="benefit-icon">{$b['icon']}</div>
  <h4 class="benefit-title">{$b['title']}</h4>
  <p class="benefit-text">{$b['text']}</p>
</div>
HTML;
}

// Testimonials
$t_html = '';
foreach ($testimonials as $i => $t) {
    $delay   = $i + 1;
    $stars   = render_stars((int)($t['rating'] ?? 5));
    $t_html .= <<<HTML
<div class="testimonial-card reveal reveal-delay-{$delay}">
  <div class="testimonial-stars">{$stars}</div>
  <p class="testimonial-text">"{$t['text']}"</p>
  <div class="testimonial-author">
    <div class="testimonial-avatar">{$t['initials']}</div>
    <div>
      <div class="testimonial-name">{$t['name']}</div>
      <div class="testimonial-date">{$t['date']}</div>
    </div>
  </div>
</div>
HTML;
}

$hero_bg     = h($hero['bg_image'] ?? '');
$hero_badge  = h($hero['badge']    ?? 'Personal Training Wien');
$hero_h1     = h($hero['headline_line1'] ?? 'Erreich die Form');
$hero_h2     = h($hero['headline_line2'] ?? 'deines Lebens');
$hero_sub    = h($hero['subheadline'] ?? '');
$hero_price  = h($hero['price_note'] ?? '');
$hero_cta1   = h($hero['cta_primary'] ?? 'Gratis Probetraining buchen');
$hero_cta2   = h($hero['cta_secondary'] ?? 'Leistungen entdecken');
$hero_rating = h($hero['rating'] ?? '4.9');
$why_img     = h($why['image'] ?? '');
$why_label   = h($why['label'] ?? 'Warum FlexFit');
$why_headline= nl2br(h($why['headline'] ?? ''));
$why_sub     = h($why['subtext'] ?? '');
$why_badge_n = h($why['image_badge_number'] ?? '15+');
$why_badge_l = h($why['image_badge_label']  ?? 'Jahre Erfahrung');
$trainer_photo = h($trainer['photo'] ?? '');
$trainer_name  = h($trainer['name']  ?? 'Patrick K. Miller');
$trainer_title = h($trainer['title'] ?? '');
$trainer_bio   = h($trainer['bio']   ?? '');
$trainer_bio2  = h($trainer['bio2']  ?? '');
$benefits_label = h($benefits['label']    ?? '');
$benefits_head  = h($benefits['headline'] ?? '');
$benefits_sub   = h($benefits['subtext']  ?? '');
$contact_head   = h($contact_d['headline'] ?? 'Bereit für deine Transformation?');

?>
<section class="hero" id="hero">
  <!-- Fallback bg image -->
  <div class="hero-bg" id="heroBg" style="background-image:url('<?= $hero_bg ?>')"></div>

  <!-- Background video (from FlexFit server) -->
  <div class="hero-bg-video" aria-hidden="true">
    <video autoplay muted loop playsinline
      src="https://demo.bmsdigitalsolutions.com/assets/video/hero-bg.mp4">
    </video>
  </div>

  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        <div class="hero-badge-dot"></div>
        <span class="hero-badge-text"><?= $hero_badge ?></span>
      </div>
      <h1 class="hero-headline">
        <?= $hero_h1 ?><br>
        <span class="accent"><?= $hero_h2 ?></span>
      </h1>
      <p class="hero-sub"><?= $hero_sub ?><br>
        <span class="hero-price"><?= $hero_price ?></span>
      </p>
      <div class="hero-ctas">
        <a href="/probetraining" class="btn btn-primary btn-lg"><?= $hero_cta1 ?> →</a>
        <a href="#leistungen" class="btn btn-outline btn-lg"><?= $hero_cta2 ?></a>
      </div>
      <div class="hero-trust">
        <div class="hero-stars">★★★★★</div>
        <p class="hero-trust-text"><strong><?= $hero_rating ?>/5</strong> – 65 Google-Bewertungen</p>
      </div>
    </div>
  </div>
  <div class="hero-scroll"><div class="scroll-line"></div><span>Scroll</span></div>
</section>

<section class="stats-strip" id="stats">
  <div class="container"><div class="stats-grid"><?= $stat_html ?></div></div>
</section>

<section class="partners-section">
  <div class="container">
    <p class="partners-label">Bekannt aus &amp; Zusammenarbeit mit</p>
    <div class="partners-logos"><?= $partner_html ?></div>
  </div>
</section>

<section class="why-section section-pad" id="warum">
  <div class="container">
    <div class="why-grid">
      <div class="why-image-wrap reveal-left">
        <img src="<?= $why_img ?>" alt="FlexFit Training" loading="lazy" style="width:100%;height:100%;object-fit:cover">
        <div class="why-image-badge">
          <strong><?= $why_badge_n ?></strong>
          <span><?= $why_badge_l ?></span>
        </div>
      </div>
      <div class="why-content reveal-right">
        <span class="section-label"><?= $why_label ?></span>
        <h2 class="section-title"><?= $why_headline ?></h2>
        <p class="section-subtitle"><?= $why_sub ?></p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin:20px 0"><?= $goal_badges ?></div>
        <div class="why-features"><?= $why_features_html ?></div>
        <div style="margin-top:32px">
          <a href="/probetraining" class="btn btn-primary">Gratis Probetraining buchen →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="services-section section-pad" id="leistungen">
  <div class="container">
    <div class="text-center reveal">
      <span class="section-label">Unsere Angebote</span>
      <h2 class="section-title">Training, das zu dir passt</h2>
    </div>
    <div class="services-grid"><?= $service_html ?></div>
  </div>
</section>

<section class="trainer-section section-pad" id="trainer">
  <div class="container">
    <div class="trainer-grid">
      <div class="trainer-image-wrap reveal-left">
        <img src="<?= $trainer_photo ?>" alt="<?= $trainer_name ?>" loading="lazy" style="width:100%;aspect-ratio:3/4;object-fit:cover;object-position:top">
      </div>
      <div class="trainer-content reveal-right">
        <span class="section-label">Dein Coach</span>
        <h2 class="trainer-name"><?= $trainer_name ?></h2>
        <p class="trainer-title"><?= $trainer_title ?></p>
        <p class="trainer-bio"><?= $trainer_bio ?></p>
        <p class="trainer-bio"><?= $trainer_bio2 ?></p>
        <div class="trainer-specs"><?= $trainer_specs_html ?></div>
        <div style="margin-bottom:32px">
          <p style="color:rgba(255,255,255,.5);font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:12px">Spezialisierungen</p>
          <div style="display:flex;flex-direction:column;gap:8px"><?= $trainer_specializations_html ?></div>
        </div>
        <a href="/probetraining" class="btn btn-primary">Mit Patrick trainieren →</a>
      </div>
    </div>
  </div>
</section>

<section class="benefits-section section-pad">
  <div class="container">
    <div class="benefits-header reveal">
      <span class="section-label" style="justify-content:center"><?= $benefits_label ?></span>
      <h2><?= $benefits_head ?></h2>
      <p style="color:rgba(255,255,255,.55);max-width:560px;margin:16px auto 0"><?= $benefits_sub ?></p>
    </div>
    <div class="benefits-grid"><?= $benefit_html ?></div>
  </div>
</section>

<section class="testimonials-section section-pad" id="bewertungen">
  <div class="container">
    <div class="reviews-header reveal">
      <div>
        <span class="section-label">Was Kunden sagen</span>
        <h2>Echte Ergebnisse, echte Menschen</h2>
      </div>
      <div class="reviews-score">
        <div class="reviews-big-score">4.9</div>
        <div class="reviews-meta">
          <div class="reviews-stars">★★★★★</div>
          <div class="reviews-count">65 Google-Bewertungen</div>
        </div>
      </div>
    </div>
    <div class="testimonials-grid"><?= $t_html ?></div>
    <div style="text-align:center;margin-top:40px">
      <a href="#" class="btn btn-outline-dark">Alle Bewertungen auf Google →</a>
    </div>
  </div>
</section>

<section class="cta-banner">
  <div class="container"><div class="reveal">
    <span class="section-label" style="justify-content:center;margin-bottom:16px">Kostenlos &amp; unverbindlich</span>
    <h2>Starte heute mit deinem<br>gratis Probetraining</h2>
    <p>Lerne Patrick und das Studio kennen – ohne Verpflichtung, ohne Risiko.</p>
    <div class="cta-banner-actions">
      <a href="/probetraining" class="btn btn-primary btn-lg">Jetzt Probetraining buchen →</a>
      <a href="/kontakt" class="btn btn-outline btn-lg">Kontakt aufnehmen</a>
    </div>
    <p class="cta-note">✓ Kostenlos &nbsp;·&nbsp; ✓ Unverbindlich &nbsp;·&nbsp; ✓ Kein Vertrag</p>
  </div></div>
</section>

<?php echo render_footer($c); ?>
