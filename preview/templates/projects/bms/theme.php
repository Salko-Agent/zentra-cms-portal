<?php
/**
 * BMS Digital Solutions – Preview Theme
 * Overrides render_head(), render_navbar(), render_footer() for the BMS project.
 * Loaded by renderer.php before any BMS page template.
 */

if (!function_exists('bms_render_head')) {

function bms_render_head(string $title, array $seo = []): string {
    $robots  = !empty($seo['noindex']) ? 'noindex, nofollow' : 'index, follow';
    $desc    = htmlspecialchars($seo['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $desc_tag = $desc ? "<meta name=\"description\" content=\"{$desc}\">" : '';
    return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<meta name="robots" content="{$robots}">
{$desc_tag}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
/* BMS Design System – Preview Styles */
:root {
  --bms-black: #0a0a0a;
  --bms-dark: #111111;
  --bms-card: #1a1a1a;
  --bms-border: rgba(255,255,255,.08);
  --bms-accent: #6366f1;
  --bms-accent2: #a855f7;
  --bms-text: #e5e5e5;
  --bms-muted: #888;
  --bms-white: #ffffff;
  --bms-r: 12px;
  --font-body: 'Inter', sans-serif;
  --font-head: 'Plus Jakarta Sans', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { background: var(--bms-black); color: var(--bms-text); font-family: var(--font-body); font-size: 16px; line-height: 1.6; }
a { color: var(--bms-accent); text-decoration: none; }
a:hover { color: var(--bms-accent2); }
img { max-width: 100%; height: auto; display: block; }
h1,h2,h3,h4 { font-family: var(--font-head); color: var(--bms-white); line-height: 1.2; }
h1 { font-size: clamp(2rem, 5vw, 3.5rem); }
h2 { font-size: clamp(1.6rem, 3.5vw, 2.5rem); }
h3 { font-size: 1.25rem; }
p { color: var(--bms-text); }
.container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
.section-pad { padding: 96px 0; }
.text-center { text-align: center; }
.badge { display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:999px;font-size:.75rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase; }
.badge-accent { background:rgba(99,102,241,.15);color:var(--bms-accent);border:1px solid rgba(99,102,241,.3); }
.card { background:var(--bms-card);border:1px solid var(--bms-border);border-radius:var(--bms-r);padding:32px; }
.btn { display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;transition:.2s;border:none; }
.btn-primary { background:linear-gradient(135deg,var(--bms-accent),var(--bms-accent2));color:#fff; }
.btn-primary:hover { opacity:.9;color:#fff; }
.btn-outline { background:transparent;border:1px solid var(--bms-border);color:var(--bms-text); }
.btn-outline:hover { border-color:var(--bms-accent);color:var(--bms-accent); }
.btn-sm { padding:8px 16px;font-size:.82rem; }

/* Navbar */
.bms-navbar { position:sticky;top:0;z-index:100;background:rgba(10,10,10,.9);backdrop-filter:blur(12px);border-bottom:1px solid var(--bms-border);padding:0 24px; }
.bms-navbar-inner { max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:64px; }
.bms-logo { font-family:var(--font-head);font-weight:800;font-size:1.15rem;color:var(--bms-white);letter-spacing:-.02em; }
.bms-logo span { color:var(--bms-accent); }
.bms-nav { display:flex;gap:4px; }
.bms-nav a { padding:8px 14px;border-radius:8px;color:var(--bms-muted);font-size:.875rem;font-weight:500;transition:.15s; }
.bms-nav a:hover { color:var(--bms-white);background:rgba(255,255,255,.06); }

/* Hero */
.bms-hero { padding:120px 0 80px;text-align:center; }
.bms-hero h1 { margin:16px 0 20px; }
.bms-hero p { color:var(--bms-muted);max-width:600px;margin-inline:auto;font-size:1.05rem;margin-bottom:36px; }
.bms-hero-actions { display:flex;gap:12px;justify-content:center;flex-wrap:wrap; }

/* Stats */
.bms-stats { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;padding:48px 0; }
.bms-stat { text-align:center; }
.bms-stat-number { font-family:var(--font-head);font-size:2.5rem;font-weight:800;color:var(--bms-white);line-height:1; }
.bms-stat-label { font-size:.82rem;color:var(--bms-muted);margin-top:4px; }

/* FAQ */
.bms-faq-item { border:1px solid var(--bms-border);border-radius:var(--bms-r);margin-bottom:8px;overflow:hidden; }
.bms-faq-btn { width:100%;display:flex;justify-content:space-between;align-items:center;padding:18px 20px;background:var(--bms-card);border:none;cursor:pointer;text-align:left;font-size:.95rem;font-weight:600;font-family:inherit;color:var(--bms-white);gap:12px; }
.bms-faq-body { display:none;padding:0 20px 18px;background:var(--bms-card);color:var(--bms-muted);line-height:1.7; }

/* Timeline */
.bms-timeline { max-width:700px;margin-inline:auto;position:relative; }
.bms-timeline::before { content:'';position:absolute;left:24px;top:0;bottom:0;width:2px;background:var(--bms-border); }
.bms-timeline-item { display:flex;gap:20px;margin-bottom:32px;align-items:flex-start; }
.bms-timeline-dot { flex-shrink:0;width:48px;height:48px;border-radius:50%;background:var(--bms-card);border:2px solid var(--bms-accent);display:flex;align-items:center;justify-content:center;font-size:1.2rem;z-index:1;position:relative; }
.bms-timeline-body { padding-top:8px; }

/* Portfolio */
.bms-portfolio-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px; }
.bms-portfolio-card { background:var(--bms-card);border:1px solid var(--bms-border);border-radius:var(--bms-r);overflow:hidden;transition:.2s; }
.bms-portfolio-card:hover { border-color:var(--bms-accent); }
.bms-portfolio-img { width:100%;aspect-ratio:16/9;object-fit:cover;background:rgba(99,102,241,.1); }
.bms-portfolio-body { padding:20px; }

/* Footer */
.bms-footer { border-top:1px solid var(--bms-border);padding:48px 0 24px;margin-top:0; }
.bms-footer-grid { display:grid;grid-template-columns:2fr 1fr 1fr;gap:40px;margin-bottom:32px; }
.bms-footer-bottom { border-top:1px solid var(--bms-border);padding-top:20px;display:flex;justify-content:space-between;align-items:center;font-size:.82rem;color:var(--bms-muted); }
.bms-footer-links { display:flex;gap:16px; }
.bms-footer-links a { color:var(--bms-muted);font-size:.82rem; }
.bms-footer-links a:hover { color:var(--bms-white); }
.bms-footer-nav-title { font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--bms-muted);margin-bottom:12px; }
.bms-footer-nav a { display:block;color:var(--bms-muted);font-size:.875rem;margin-bottom:8px; }
.bms-footer-nav a:hover { color:var(--bms-white); }

/* CTA Banner */
.bms-cta { background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(168,85,247,.1));border:1px solid rgba(99,102,241,.2);border-radius:var(--bms-r);padding:64px 40px;text-align:center;margin:0; }
.bms-cta h2 { margin-bottom:12px; }
.bms-cta p { color:var(--bms-muted);margin-bottom:28px; }
.bms-cta-actions { display:flex;gap:12px;justify-content:center;flex-wrap:wrap; }

/* Responsive */
@media (max-width: 768px) {
  .bms-footer-grid { grid-template-columns:1fr; }
  .bms-nav { display:none; }
  .section-pad { padding:64px 0; }
}
</style>
</head>
<body>
HTML;
}

function bms_render_navbar(array $content): string {
    $site = $content['site'] ?? [];
    $nav = [
        ['/', 'Home'],
        ['/leistungen', 'Leistungen'],
        ['/portfolio', 'Portfolio'],
        ['/kontakt', 'Kontakt'],
    ];
    $links = '';
    foreach ($nav as [$url, $label]) {
        $links .= "<a href=\"{$url}\">{$label}</a>\n";
    }
    return <<<HTML
<nav class="bms-navbar">
  <div class="bms-navbar-inner">
    <div class="bms-logo">BMS<span>.</span></div>
    <nav class="bms-nav">{$links}</nav>
    <a href="/kontakt" class="btn btn-primary btn-sm">📅 Erstgespräch</a>
  </div>
</nav>
HTML;
}

function bms_render_footer(array $content): string {
    $site  = $content['site'] ?? [];
    $email = htmlspecialchars($site['email'] ?? 'support@bmsdigitalsolutions.com', ENT_QUOTES, 'UTF-8');
    $year  = date('Y');
    return <<<HTML
<footer class="bms-footer">
  <div class="container">
    <div class="bms-footer-grid">
      <div>
        <div class="bms-logo" style="margin-bottom:12px">BMS<span>.</span></div>
        <p style="color:var(--bms-muted);font-size:.875rem;margin-bottom:16px">Ihr Partner für digitalen Erfolg in Wien.<br>Webentwicklung · Funnels · IT-Support · Software</p>
        <a href="mailto:{$email}" style="color:var(--bms-muted);font-size:.82rem">{$email}</a>
      </div>
      <div>
        <p class="bms-footer-nav-title">Leistungen</p>
        <div class="bms-footer-nav">
          <a href="/leistungen/websites">Webentwicklung</a>
          <a href="/leistungen/funnels">Funnels</a>
          <a href="/leistungen/software">Software</a>
          <a href="/leistungen/it-support">IT-Support</a>
        </div>
      </div>
      <div>
        <p class="bms-footer-nav-title">Unternehmen</p>
        <div class="bms-footer-nav">
          <a href="/portfolio">Portfolio</a>
          <a href="/kontakt">Kontakt</a>
          <a href="/impressum">Impressum</a>
          <a href="/datenschutz">Datenschutz</a>
        </div>
      </div>
    </div>
    <div class="bms-footer-bottom">
      <p>© {$year} BMS Digital Solutions – Salih Batanovic</p>
      <div class="bms-footer-links">
        <a href="/impressum">Impressum</a>
        <a href="/datenschutz">Datenschutz</a>
      </div>
    </div>
  </div>
</footer>
</body></html>
HTML;
}

} // end if !function_exists
