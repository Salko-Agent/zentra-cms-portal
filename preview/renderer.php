<?php
// ============================================================
// Zentra Preview – Renderer
// Called by api/preview.php
// $slug = page slug, $content = full content array from load_content()
// ============================================================

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function render_stars(int $n = 5): string {
    return '<span style="color:#FBBF24">' . str_repeat('★', max(0, min(5, $n))) . '</span>';
}

function render_head(string $title, array $seo = []): string {
    $robots   = !empty($seo['noindex']) ? 'noindex, nofollow' : 'index, follow';
    $desc     = h($seo['description'] ?? '');
    $og_title = h($seo['og_title']    ?? $title);
    $og_desc  = h($seo['og_description'] ?? ($seo['description'] ?? ''));
    $og_img   = h($seo['og_image']    ?? '');
    $desc_tag    = $desc    ? "<meta name=\"description\" content=\"{$desc}\">" : '';
    $og_img_tag  = $og_img  ? "<meta property=\"og:image\" content=\"{$og_img}\">" : '';
    $css_v    = @filemtime(__DIR__ . '/assets/css/main.css') ?: time();
    $js_v     = @filemtime(__DIR__ . '/assets/js/main.js')  ?: time();
    return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<meta name="robots" content="{$robots}">
{$desc_tag}
<meta property="og:title" content="{$og_title}">
<meta property="og:description" content="{$og_desc}">
{$og_img_tag}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Bebas+Neue&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/preview/assets/css/main.css?v={$css_v}">
</head>
<body>
HTML;
}

function render_navbar(array $content): string {
    $logo = h($content['site']['logo'] ?? '');
    $nav_items = [
        ['/personal-training', 'Personal Training'],
        ['/firmenfitness',     'Firmenfitness'],
        ['/physiotherapie',    'Physiotherapie'],
        ['/studio',            'Studio'],
        ['/team',              'Team'],
        ['/blog',              'Blog'],
        ['/kontakt',           'Kontakt'],
    ];
    $links = '';
    foreach ($nav_items as [$url, $label]) {
        $links .= "<a href=\"{$url}\" class=\"nav-link\">{$label}</a>\n";
    }
    $mobile_links = '';
    foreach ($nav_items as [$url, $label]) {
        $mobile_links .= "<a href=\"{$url}\" class=\"nav-link\">{$label}</a>\n";
    }
    return <<<HTML
<nav class="navbar scrolled" id="navbar">
  <div class="container">
    <div class="navbar-inner">
      <a href="/" class="navbar-logo">
        <img src="{$logo}" alt="FlexFit" width="44" height="44">
        <span class="navbar-logo-text">Smart<span>Fit</span></span>
      </a>
      <nav class="navbar-nav">{$links}</nav>
      <div class="navbar-actions">
        <a href="/probetraining" class="btn btn-primary btn-sm">Gratis Probetraining</a>
        <button class="nav-toggle" id="navToggle"><span></span><span></span><span></span></button>
      </div>
    </div>
  </div>
</nav>
<div class="mobile-nav" id="mobileNav">
  <button class="mobile-nav-close" id="mobileNavClose">✕</button>
  {$mobile_links}
  <a href="/probetraining" class="btn btn-primary btn-lg" style="margin-top:16px">Gratis Probetraining buchen</a>
</div>
HTML;
}

function render_footer(array $content): string {
    $site  = $content['site'] ?? [];
    $logo  = h($site['logo']      ?? '');
    $email = h($site['email']     ?? '');
    $insta = h($site['instagram'] ?? '#');
    $fb    = h($site['facebook']  ?? '#');
    $year  = date('Y');
    $js_v  = @filemtime(__DIR__ . '/assets/js/main.js') ?: time();
    return <<<HTML
<div class="floating-cta" id="floatingCta">
  <span class="floating-cta-text">Bereit loszulegen?</span>
  <a href="/probetraining" class="btn btn-primary">Gratis Probetraining →</a>
</div>
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="{$logo}" alt="FlexFit" width="36" height="36">
          <span class="footer-logo-name">FlexFit</span>
        </div>
        <p class="footer-tagline">Personal Training in Wien –<br>individuell, wissenschaftlich fundiert.</p>
        <div class="footer-socials">
          <a href="{$insta}" target="_blank" class="social-btn">IG</a>
          <a href="{$fb}" target="_blank" class="social-btn">FB</a>
        </div>
      </div>
      <div>
        <p class="footer-nav-title">Leistungen</p>
        <div class="footer-nav-links">
          <a href="/personal-training" class="footer-nav-link">Personal Training</a>
          <a href="/firmenfitness" class="footer-nav-link">Firmenfitness</a>
          <a href="/physiotherapie" class="footer-nav-link">Physiotherapie</a>
        </div>
      </div>
      <div>
        <p class="footer-nav-title">Studio</p>
        <div class="footer-nav-links">
          <a href="/studio" class="footer-nav-link">Unser Studio</a>
          <a href="/team" class="footer-nav-link">Das Team</a>
          <a href="/probetraining" class="footer-nav-link">Gratis Probetraining</a>
        </div>
      </div>
      <div>
        <p class="footer-nav-title">Kontakt</p>
        <div class="footer-nav-links">
          <a href="https://maps.google.com/?q=Musterstraße+50,+1080+Wien" target="_blank" class="footer-nav-link">Musterstraße 12, 1010 Wien</a>
          <a href="mailto:{$email}" class="footer-nav-link">{$email}</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="footer-copy">© {$year} FlexFit Personal Training e.U. · Patrick K. Miller</p>
      <div class="footer-legal">
        <a href="/impressum">Impressum</a>
        <a href="/datenschutz">Datenschutz</a>
      </div>
    </div>
  </div>
</footer>
<script src="/preview/assets/js/main.js?v={$js_v}"></script>
</body></html>
HTML;
}

// ── PAGE ROUTER ──────────────────────────────────────────────

/**
 * Render a page for a given project.
 * Lookup order:
 *   1. preview/templates/projects/{project_key}/pages/{slug}.php   ← project-specific
 *   2. preview/templates/pages/{slug}.php                           ← FlexFit fallback
 *
 * If a project-specific theme.php exists it is loaded first, allowing the
 * project to define its own render_navbar() / render_footer() / CSS links.
 */
function render_page(string $slug, array $c, string $project_key = 'flexfit'): void {
    $project_key = preg_replace('/[^a-z0-9_-]/', '', strtolower($project_key));

    // Load project-specific theme overrides (optional)
    $theme_file = __DIR__ . '/templates/projects/' . $project_key . '/theme.php';
    if ($project_key !== 'flexfit' && file_exists($theme_file)) {
        require_once $theme_file;
    }

    // Find page template
    $project_page = __DIR__ . '/templates/projects/' . $project_key . '/pages/' . $slug . '.php';
    $default_page = __DIR__ . '/templates/pages/' . $slug . '.php';

    if (file_exists($project_page)) {
        require $project_page;
    } elseif (file_exists($default_page)) {
        require $default_page;
    } else {
        echo render_head('404 – Seite nicht gefunden');
        echo render_navbar($c);
        echo '<div class="container" style="padding:120px 20px;text-align:center"><h1>404</h1><p>Seite nicht gefunden.</p></div>';
        echo render_footer($c);
    }
}
