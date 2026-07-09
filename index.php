<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
auth_start();
if (!empty($_SESSION['user_id'])) { header('Location: /admin/dashboard.php'); exit; }
?><!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Zentra CMS | Website selbst verwalten. Ohne WordPress.</title>
  <meta name="description" content="Zentra ist ein maßgeschneidertes CMS für handcodierte Websites. Inhalte bearbeiten, SEO verwalten, Bilder austauschen. Gebaut von BMS Digital Solutions in Wien.">
  <meta name="robots" content="index,follow">
  <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:       #3B82F6;
  --blue-light: #60A5FA;
  --blue-dim:   rgba(59,130,246,.12);
  --blue-glow:  rgba(59,130,246,.35);
  --cyan:       #22D3EE;
  --indigo:     #6366F1;
  --bg:         #03050F;
  --bg1:        #060917;
  --bg2:        #090E22;
  --bg3:        #0D1530;
  --border:     rgba(59,130,246,.1);
  --border2:    rgba(59,130,246,.18);
  --text:       #E2E8FF;
  --text2:      #7B93C4;
  --text3:      #3A5180;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

/* NOISE */
body::after{
  content:'';position:fixed;inset:0;z-index:9999;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  opacity:.028;mix-blend-mode:overlay;background-size:200px 200px;
}

/* NAV */
nav{
  position:fixed;top:0;left:0;right:0;z-index:200;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 clamp(20px,5vw,80px);height:62px;
  background:rgba(3,5,15,.7);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  border-bottom:1px solid var(--border);transition:background .3s,border-color .3s;
}
nav.scrolled{background:rgba(3,5,15,.95);border-color:var(--border2)}
.nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo{
  width:32px;height:32px;border-radius:8px;
  box-shadow:0 0 20px rgba(59,130,246,.4);
}
.nav-name{font-weight:700;font-size:.95rem;color:#fff;letter-spacing:-.01em}
.nav-name em{color:var(--blue);font-style:normal}
.nav-links{display:flex;gap:4px}
.nav-link{color:var(--text2);text-decoration:none;font-size:.84rem;font-weight:500;padding:6px 14px;border-radius:7px;transition:.15s}
.nav-link:hover{color:var(--text);background:rgba(59,130,246,.08)}
.btn-nav{
  background:var(--blue);color:#fff;text-decoration:none;font-size:.84rem;font-weight:700;
  padding:8px 20px;border-radius:8px;transition:.2s;box-shadow:0 0 18px rgba(59,130,246,.3);
}
.btn-nav:hover{background:var(--blue-light);box-shadow:0 0 32px rgba(59,130,246,.55);transform:translateY(-1px)}
@media(max-width:640px){.nav-links{display:none}}

/* HERO */
.hero{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  padding:100px clamp(20px,5vw,80px) 80px;background:#000;
  text-align:center;position:relative;overflow:hidden;isolation:isolate;
}
/* fade hero bottom into page bg */
.hero::after{content:'';position:absolute;bottom:0;left:0;right:0;height:320px;background:linear-gradient(transparent,#000 60%,var(--bg));z-index:8;pointer-events:none}

/* ── LAVA LAMP (CSS filter metaball trick) ── */
.lava-wrap{
  position:absolute;top:0;left:0;right:0;height:75%;overflow:hidden;z-index:0;pointer-events:none;
  background:#000;
  filter:blur(28px) contrast(18);
  -webkit-mask-image:linear-gradient(to bottom,black 40%,transparent 100%);
  mask-image:linear-gradient(to bottom,black 40%,transparent 100%);
}
.blob{position:absolute;border-radius:50%}
/* Colors: blue channel must be dominant (>128) for contrast() trick to keep them visible */
.b1{width:440px;height:440px;left:5%;bottom:-15%;background:#0050EE;animation:lava1 18s ease-in-out infinite}
.b2{width:350px;height:350px;right:5%;bottom:-12%;background:#002FF8;animation:lava2 14s ease-in-out infinite}
.b3{width:310px;height:320px;left:38%;bottom:-14%;background:#0060DD;animation:lava3 16s ease-in-out infinite}
.b4{width:280px;height:280px;left:16%;top:-10%;background:#1038EE;animation:lava4 20s ease-in-out infinite}
.b5{width:240px;height:240px;right:20%;top:2%;background:#0040FF;animation:lava5 12s ease-in-out infinite}
.b6{width:320px;height:300px;left:56%;top:-12%;background:#0034CC;animation:lava6 22s ease-in-out infinite}
@keyframes lava1{
  0%,100%{transform:translate(0,0) scale(1)}
  30%{transform:translate(55px,-360px) scale(1.14)}
  65%{transform:translate(-25px,-200px) scale(.9)}
  85%{transform:translate(40px,-420px) scale(1.08)}
}
@keyframes lava2{
  0%,100%{transform:translate(0,0) scale(1)}
  25%{transform:translate(-75px,-320px) scale(1.22)}
  60%{transform:translate(50px,-180px) scale(.86)}
  80%{transform:translate(-40px,-380px) scale(1.14)}
}
@keyframes lava3{
  0%,100%{transform:translate(0,0) scale(1)}
  40%{transform:translate(-30px,-440px) scale(1.28)}
  72%{transform:translate(60px,-220px) scale(.8)}
}
@keyframes lava4{
  0%,100%{transform:translate(0,0) scale(1)}
  35%{transform:translate(50px,330px) scale(1.14)}
  70%{transform:translate(-70px,440px) scale(.86)}
}
@keyframes lava5{
  0%,100%{transform:translate(0,0) scale(1)}
  50%{transform:translate(-50px,320px) scale(1.3)}
  76%{transform:translate(40px,170px) scale(.82)}
}
@keyframes lava6{
  0%,100%{transform:translate(0,0) scale(1)}
  45%{transform:translate(30px,360px) scale(1.18)}
  80%{transform:translate(-60px,220px) scale(.9)}
}

/* grid overlay – sits above lava */
.hero::before{
  content:'';position:absolute;inset:0;z-index:2;pointer-events:none;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
  background-size:80px 80px;
  -webkit-mask-image:radial-gradient(ellipse 90% 70% at 50% 0%,black 0%,transparent 80%);
  mask-image:radial-gradient(ellipse 90% 70% at 50% 0%,black 0%,transparent 80%);
}
.hero-inner{max-width:820px;position:relative;z-index:10}

.hero-badge{
  display:inline-flex;align-items:center;gap:8px;padding:6px 16px;
  border:1px solid var(--border2);border-radius:999px;
  font-size:.72rem;font-weight:600;color:var(--blue-light);letter-spacing:.07em;text-transform:uppercase;
  margin-bottom:28px;
  background:linear-gradient(90deg,rgba(59,130,246,.08) 0%,rgba(99,102,241,.12) 50%,rgba(59,130,246,.08) 100%);
  background-size:200% 100%;animation:badgeShimmer 3s linear infinite;
  box-shadow:0 0 20px rgba(59,130,246,.12);
}
@keyframes badgeShimmer{0%{background-position:-200% center}100%{background-position:200% center}}

h1{font-size:clamp(2.8rem,6.5vw,5rem);font-weight:900;line-height:1.08;letter-spacing:-.04em;color:#fff;margin-bottom:22px}
h1 .grad{
  background:linear-gradient(135deg,#93C5FD 0%,#3B82F6 35%,#6366F1 65%,#22D3EE 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:inline-block;
}
.hero-sub{font-size:clamp(.95rem,1.8vw,1.15rem);color:var(--text2);max-width:560px;margin:0 auto 40px;line-height:1.75}
.hero-cta{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:64px}
.btn-primary{
  background:var(--blue);color:#fff;text-decoration:none;font-size:.95rem;font-weight:700;
  padding:14px 32px;border-radius:10px;transition:.22s;display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 0 28px rgba(59,130,246,.4);
}
.btn-primary:hover{background:var(--blue-light);transform:translateY(-2px);box-shadow:0 8px 48px rgba(59,130,246,.6)}
.btn-ghost{
  background:transparent;color:var(--text);text-decoration:none;font-size:.95rem;font-weight:600;
  padding:14px 28px;border-radius:10px;border:1px solid var(--border2);transition:.22s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-ghost:hover{border-color:var(--blue);color:var(--blue-light);background:var(--blue-dim)}

.hero-stats{display:flex;gap:48px;justify-content:center;flex-wrap:wrap;padding:32px 40px 0;border-top:1px solid var(--border);position:relative;z-index:12;background:rgba(0,0,0,.85);border-radius:16px;margin:0 -20px;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
.stat{text-align:center}
.stat-n{
  font-size:2rem;font-weight:800;letter-spacing:-.03em;line-height:1;
  color:#fff;
}
.stat-l{font-size:.68rem;color:var(--text2);text-transform:uppercase;letter-spacing:.1em;margin-top:5px}

/* SECTIONS */
section{padding:clamp(64px,9vw,128px) clamp(20px,5vw,80px);position:relative;z-index:10}
.container{max-width:1100px;margin:0 auto}
.section-tag{display:inline-block;font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--blue);margin-bottom:12px}
h2{font-size:clamp(1.9rem,4vw,3rem);font-weight:800;letter-spacing:-.03em;color:#fff;line-height:1.12;margin-bottom:18px}
.lead{font-size:1.05rem;color:var(--text2);max-width:560px;line-height:1.8;margin-bottom:52px}
.divider{height:1px;background:var(--border);position:relative;z-index:10}

/* FEATURES */
.feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media(max-width:768px){.feat-grid{grid-template-columns:1fr}}
.feat-card{background:var(--bg1);border:1px solid var(--border);border-radius:16px;padding:32px;transition:border-color .2s,background .2s}
.feat-card:hover{background:var(--bg2);border-color:var(--border2)}
.feat-card-icon{font-size:1.5rem;margin-bottom:16px;display:block}
.feat-card h3{font-size:1.05rem;font-weight:700;color:#fff;margin-bottom:6px}
.feat-card-sub{font-size:.78rem;color:var(--blue);font-weight:600;margin-bottom:16px}
.feat-card ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:10px}
.feat-card li{font-size:.85rem;color:var(--text2);display:flex;align-items:flex-start;gap:8px;line-height:1.5}
.feat-card li::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--blue);flex-shrink:0;margin-top:7px}

/* SPLIT */
.split{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
@media(max-width:768px){.split{grid-template-columns:1fr}}

/* MOCKUP */
.mockup{
  background:var(--bg2);border:1px solid var(--border2);border-radius:16px;padding:20px;
  box-shadow:0 0 80px rgba(59,130,246,.12),0 0 0 1px rgba(59,130,246,.08);
  animation:floatY 5s ease-in-out infinite;
}
@keyframes floatY{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.mock-bar{display:flex;align-items:center;gap:6px;margin-bottom:14px}
.mock-dot{width:10px;height:10px;border-radius:50%}
.mock-dot:nth-child(1){background:#ff5f57}.mock-dot:nth-child(2){background:#febc2e}.mock-dot:nth-child(3){background:#28c840}
.mock-url{flex:1;background:var(--bg3);border-radius:5px;height:20px;margin-left:8px;display:flex;align-items:center;padding:0 10px;font-size:.65rem;color:var(--text3)}
.mock-body{display:grid;grid-template-columns:180px 1fr;gap:1px;background:var(--border);border-radius:8px;overflow:hidden;min-height:300px}
.mock-side{background:#040816;padding:14px 0}
.mock-item{padding:7px 14px;font-size:.72rem;color:var(--text3);display:flex;align-items:center;gap:7px}
.mock-item.on{background:var(--blue-dim);color:var(--blue-light);font-weight:600;border-right:2px solid var(--blue)}
.mock-main{background:#060E20;padding:18px}
.mock-title{font-size:.78rem;font-weight:700;color:#fff;margin-bottom:12px}
.mock-card{background:var(--bg3);border:1px solid var(--border);border-radius:7px;padding:12px;margin-bottom:8px}
.mock-lbl{font-size:.58rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px}
.mock-inp{background:rgba(59,130,246,.07);border:1px solid var(--border);border-radius:4px;height:26px;width:100%;margin-bottom:7px}
.mock-inp.s{width:55%}
.mock-row-r{background:#060E1C;border:1px solid var(--border);border-radius:4px;padding:7px;margin-bottom:4px;display:flex;gap:5px}
.mock-rf{background:rgba(59,130,246,.07);border-radius:3px;height:20px;flex:1}
.mock-rdel{width:20px;height:20px;background:rgba(255,80,80,.12);border-radius:3px;flex-shrink:0}
.mock-add{border:1.5px dashed var(--border2);border-radius:4px;height:26px;width:100%;margin-top:3px;display:flex;align-items:center;justify-content:center}
.mock-add span{font-size:.58rem;color:var(--blue)}
.mock-save-btn{background:var(--blue);border-radius:4px;height:28px;width:88px;margin-top:10px}

/* STEPS */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0;position:relative}
.steps::before{content:'';position:absolute;top:28px;left:12%;right:12%;height:1px;background:linear-gradient(90deg,transparent,var(--border2),var(--border2),transparent)}
@media(max-width:768px){.steps::before{display:none}}
.step{text-align:center;padding:28px 20px;position:relative}
.step-n{
  width:56px;height:56px;margin:0 auto 20px;border-radius:50%;
  background:var(--bg2);border:1.5px solid var(--border2);
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;font-weight:800;color:var(--blue);
  box-shadow:0 0 0 8px var(--bg1);position:relative;z-index:1;
  animation:stepGlow 3s ease-in-out infinite;
}
.step:nth-child(2) .step-n{animation-delay:.75s}
.step:nth-child(3) .step-n{animation-delay:1.5s}
.step:nth-child(4) .step-n{animation-delay:2.25s}
@keyframes stepGlow{0%,100%{box-shadow:0 0 0 8px var(--bg1),0 0 0 0 rgba(59,130,246,0)}50%{box-shadow:0 0 0 8px var(--bg1),0 0 28px 4px rgba(59,130,246,.35)}}
.step h3{font-size:.95rem;font-weight:700;color:#fff;margin-bottom:8px}
.step p{font-size:.84rem;color:var(--text2);line-height:1.65}

/* CHIPS */
.chip{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;margin:3px;background:var(--blue-dim);border:1px solid var(--border2);border-radius:999px;font-size:.75rem;color:var(--blue-light);font-weight:500}

/* CTA */
.cta-wrap{
  background:linear-gradient(135deg,var(--bg2) 0%,var(--bg1) 100%);
  border:1px solid var(--border2);border-radius:24px;
  padding:clamp(48px,6vw,80px);text-align:center;position:relative;overflow:hidden;
}
.cta-wrap::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 70% 90% at 50% 50%,rgba(59,130,246,.07) 0%,transparent 70%);pointer-events:none}
.cta-badge{font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--blue);margin-bottom:16px;display:block}
.cta-wrap h2{margin-bottom:16px}
.cta-wrap p{max-width:500px;margin:0 auto 36px;color:var(--text2);line-height:1.75}
.cta-bottom{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:20px;font-size:.85rem;color:var(--text3);flex-wrap:wrap}

/* MAKER */
.maker{display:flex;gap:40px;align-items:flex-start;flex-wrap:wrap;background:var(--bg2);border:1px solid var(--border2);border-radius:20px;padding:clamp(28px,4vw,48px)}
.maker-av{width:72px;height:72px;border-radius:16px;flex-shrink:0;overflow:hidden;box-shadow:0 0 32px rgba(59,130,246,.3)}
.maker-info{flex:1;min-width:200px}
.maker-name{font-size:1.15rem;font-weight:700;color:#fff;margin-bottom:3px}
.maker-role{font-size:.8rem;color:var(--blue);font-weight:600;margin-bottom:14px}
.maker-bio{font-size:.9rem;color:var(--text2);line-height:1.75;margin-bottom:18px}
.maker-link{display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:700;color:var(--blue);text-decoration:none;transition:.15s}
.maker-link:hover{color:var(--blue-light)}
.maker-cards{flex-shrink:0;display:flex;flex-direction:column;gap:10px;min-width:210px}
.info-card{background:var(--bg3);border:1px solid var(--border);border-radius:12px;padding:16px}
.info-card-title{font-size:.62rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}
.info-card-body{font-size:.8rem;color:var(--text2);line-height:1.85}
.info-card.accent{background:var(--blue-dim);border-color:var(--border2)}
.info-card.accent .info-card-title{color:var(--blue)}

/* FOOTER */
footer{padding:36px clamp(20px,5vw,80px);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;position:relative;z-index:10}
.footer-brand{display:flex;align-items:center;gap:8px;text-decoration:none;font-size:.88rem;font-weight:700;color:#fff}
.footer-brand em{color:var(--blue);font-style:normal}
.footer-text{font-size:.76rem;color:var(--text3)}
.footer-links{display:flex;gap:20px}
.footer-links a{font-size:.76rem;color:var(--text3);text-decoration:none;transition:.15s}
.footer-links a:hover{color:var(--text2)}

/* REVEAL */
.reveal{opacity:0;transform:translateY(36px);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1)}
.reveal.visible{opacity:1;transform:translateY(0)}
.d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}.d4{transition-delay:.4s}

/* CTA PULSE */
.cta-wrap .btn-primary{animation:ctaPulse 2.8s ease-in-out infinite}
@keyframes ctaPulse{0%,100%{box-shadow:0 0 28px rgba(59,130,246,.4)}50%{box-shadow:0 0 64px rgba(59,130,246,.75),0 4px 20px rgba(59,130,246,.4)}}

@media(max-width:520px){.hero-stats{gap:28px}.maker{flex-direction:column}.maker-cards{width:100%;min-width:0}}
</style>
</head>
<body>

<nav id="nav">
  <a href="/" class="nav-brand">
    <img src="/assets/img/zentra-logo.svg" alt="Zentra" class="nav-logo">
    <div class="nav-name">Zentra<em>.services</em></div>
  </a>
  <div class="nav-links">
    <a href="#features" class="nav-link">Features</a>
    <a href="#wie" class="nav-link">So funktionierts</a>
    <a href="#bms" class="nav-link">Über BMS</a>
  </div>
  <a href="/login.php" class="btn-nav">→ Anmelden</a>
</nav>

<section class="hero">
  <div class="lava-wrap">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
    <div class="blob b4"></div>
    <div class="blob b5"></div>
    <div class="blob b6"></div>
  </div>
  <div class="hero-inner">
    <div class="hero-badge"><img src="/assets/img/favicon.svg" alt="" style="width:16px;height:16px;border-radius:3px"> Content Management · Made in Vienna</div>
    <h1>Website selbst verwalten.<br><span class="grad">Ohne WordPress.</span></h1>
    <p class="hero-sub">Ein CMS gebaut für handcodierte Websites. Kein WordPress, kein Baukasten. Maßgeschneidert auf Ihre Seite, bedienbar ohne technisches Wissen.</p>
    <div class="hero-cta">
      <a href="/login.php" class="btn-primary">Zum CMS anmelden →</a>
      <a href="#features" class="btn-ghost">Features ansehen ↓</a>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-n">API-first</div><div class="stat-l">Headless Architektur</div></div>
      <div class="stat"><div class="stat-n">0 Plugins</div><div class="stat-l">Kein Bloat</div></div>
      <div class="stat"><div class="stat-n">&lt;1s</div><div class="stat-l">Content Delivery</div></div>
      <div class="stat"><div class="stat-n">100%</div><div class="stat-l">Ihre Daten</div></div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section id="features">
  <div class="container">
    <div class="section-tag reveal">Was Zentra kann</div>
    <h2 class="reveal d1">Alles was Sie brauchen.<br>Nichts was Sie nicht brauchen.</h2>
    <p class="lead reveal d2">Gebaut für Websites die von Hand entwickelt wurden. Jedes Feature existiert, weil ein echter Kunde es gebraucht hat.</p>
    <div class="feat-grid reveal d3">
      <div class="feat-card">
        <span class="feat-card-icon">✏️</span>
        <h3>Content</h3>
        <div class="feat-card-sub">Inhalte selbst verwalten</div>
        <ul>
          <li>Texte, Bilder, CTAs und Listen direkt im Browser bearbeiten</li>
          <li>Sektionen per Drag &amp; Drop sortieren, hinzufügen oder entfernen</li>
          <li>Medienverwaltung mit Upload und sofortiger Vorschau</li>
        </ul>
      </div>
      <div class="feat-card">
        <span class="feat-card-icon">&#x1F4CA;</span>
        <h3>SEO &amp; Tracking</h3>
        <div class="feat-card-sub">Sichtbarkeit ohne Entwickler</div>
        <ul>
          <li>Meta Title, Description und Open Graph pro Seite individuell</li>
          <li>Google Analytics, Tag Manager und Meta Pixel in einem Schritt</li>
          <li>Noindex-Schalter, Sitemap und saubere URL-Struktur</li>
        </ul>
      </div>
      <div class="feat-card">
        <span class="feat-card-icon">&#x1F512;</span>
        <h3>Technik</h3>
        <div class="feat-card-sub">Sicher, schnell, zuverlässig</div>
        <ul>
          <li>Content-Delivery unter einer Sekunde per Webhook-Sync</li>
          <li>CSRF-Schutz, Rate-Limiting und verschlüsselte Sessions</li>
          <li>Mobil nutzbar. Korrekturen von überall, auch vom Smartphone</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section id="wie">
  <div class="container">
    <div class="split">
      <div class="reveal">
        <div class="section-tag">So einfach ist es</div>
        <h2>Ihre Website.<br>Ihre Kontrolle.</h2>
        <p style="color:var(--text2);line-height:1.8;margin-bottom:24px">Sie bekommen ein Admin-Panel das genau zu Ihrer Website passt. Keine generischen Templates, keine unnötigen Menüs. Nur die Felder die Sie wirklich brauchen.</p>
        <div style="margin-bottom:28px">
          <span class="chip">✓ Texte und Bilder ändern</span>
          <span class="chip">✓ SEO pro Seite</span>
          <span class="chip">✓ Tracking eingebaut</span>
          <span class="chip">✓ Sofort online</span>
        </div>
        <a href="/login.php" class="btn-primary">Jetzt anmelden →</a>
      </div>
      <div class="reveal d2">
        <div class="mockup">
          <div class="mock-bar">
            <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
            <div class="mock-url">zentra.services/admin/seiten</div>
          </div>
          <div class="mock-body">
            <div class="mock-side">
              <div class="mock-item">&#x1F4CA; Dashboard</div>
              <div class="mock-item on">&#x1F4C4; Seiten</div>
              <div class="mock-item">&#x1F50D; SEO</div>
              <div class="mock-item">⚙️ Settings</div>
              <div class="mock-item">&#x1F4EC; Anfragen</div>
            </div>
            <div class="mock-main">
              <div class="mock-title">Startseite bearbeiten</div>
              <div class="mock-card">
                <div class="mock-lbl">Hero – Überschrift</div>
                <div class="mock-inp s"></div>
                <div class="mock-lbl">Hero – Unterzeile</div>
                <div class="mock-inp"></div>
              </div>
              <div class="mock-card">
                <div class="mock-lbl">Statistiken</div>
                <div class="mock-row-r"><div class="mock-rf"></div><div class="mock-rf"></div><div class="mock-rdel"></div></div>
                <div class="mock-row-r"><div class="mock-rf"></div><div class="mock-rf"></div><div class="mock-rdel"></div></div>
                <div class="mock-add"><span>＋ Eintrag hinzufügen</span></div>
              </div>
              <div class="mock-save-btn"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section style="background:var(--bg1)">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:56px">
      <div class="section-tag">Ablauf</div>
      <h2>Live in Minuten.<br>Nicht in Tagen.</h2>
    </div>
    <div class="steps">
      <div class="step reveal"><div class="step-n">1</div><h3>Projekt einrichten</h3><p>Ich konfiguriere Zentra einmalig für Ihre Website-Struktur. Seiten, Felder, Berechtigungen.</p></div>
      <div class="step reveal d1"><div class="step-n">2</div><h3>Anmelden</h3><p>Mit Ihren Zugangsdaten direkt ins Admin-Panel. Keine Installation, kein Setup Ihrerseits.</p></div>
      <div class="step reveal d2"><div class="step-n">3</div><h3>Inhalte pflegen</h3><p>Texte ändern, Bilder austauschen, SEO optimieren. Jederzeit, selbständig.</p></div>
      <div class="step reveal d3"><div class="step-n">4</div><h3>Sofort live</h3><p>Speichern genügt. Kein Deploy, kein Cache leeren. Änderungen sind unmittelbar sichtbar.</p></div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section id="anmelden">
  <div class="container">
    <div class="cta-wrap reveal">
      <span class="cta-badge">Zugang</span>
      <h2>Ihr Projekt.<br>Ihr CMS.</h2>
      <p>Zentra ist exklusiv für Kunden von BMS Digital Solutions. Mit jeder Website die ich entwickle erhalten Sie automatisch Ihren eigenen Zentra-Zugang. Fertig konfiguriert, sofort nutzbar.</p>
      <a href="/login.php" class="btn-primary" style="font-size:1rem;padding:16px 44px">Zum CMS-Login →</a>
      <div class="cta-bottom">
        <span>Noch keine Website von BMS?</span>
        <a href="https://bmsdigitalsolutions.com" target="_blank" style="color:var(--blue);text-decoration:none;font-weight:600">bmsdigitalsolutions.com →</a>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<section id="bms">
  <div class="container">
    <div class="section-tag">Über BMS</div>
    <h2 style="margin-bottom:32px">BMS Digital Solutions</h2>
    <div class="maker reveal">
      <div class="maker-av"><img src="/assets/img/zentra-logo.svg" alt="BMS" style="width:100%;height:100%"></div>
      <div class="maker-info">
        <div class="maker-name">Salih Batanovic</div>
        <div class="maker-role">Full-Stack Developer · Gründer BMS Digital Solutions</div>
        <p class="maker-bio">Zentra ist kein generisches Open-Source-Paket. Es ist ein Produkt, das aus echten Kundenprojekten entstanden ist. Ich entwickle maßgeschneiderte Websites und digitale Systeme für KMUs in Wien. Jede Website die ich ausliefere beinhaltet automatisch Zentra, damit meine Kunden ihre Inhalte selbst pflegen können, ohne mich jedes Mal kontaktieren zu müssen. Kein WordPress, keine Plugins, keine monatlichen Lizenzkosten.</p>
        <a href="https://bmsdigitalsolutions.com" target="_blank" class="maker-link">&#x1F310; bmsdigitalsolutions.com →</a>
      </div>
      <div class="maker-cards">
        <div class="info-card">
          <div class="info-card-title">Leistungen</div>
          <div class="info-card-body">&#x1F310; Webentwicklung<br>&#x1F504; Funnels &amp; Automation<br>&#x2699;&#xFE0F; Software &amp; Tools<br>&#x1F6E1; IT-Support Wien</div>
        </div>
        <div class="info-card accent">
          <div class="info-card-title">Kontakt</div>
          <div class="info-card-body">
            <a href="tel:+436764775774" style="color:var(--text2);text-decoration:none">+43 676 477 5774</a><br>
            <a href="mailto:support@bmsdigitalsolutions.com" style="color:var(--blue);text-decoration:none;font-size:.76rem">support@bmsdigitalsolutions.com</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <a href="/" class="footer-brand"><img src="/assets/img/favicon.svg" alt="" style="width:20px;height:20px;border-radius:4px"> Zentra<em>.services</em></a>
  <p class="footer-text">&copy; <?= date('Y') ?> Zentra &middot; Ein Produkt von <a href="https://bmsdigitalsolutions.com" target="_blank" style="color:var(--blue);text-decoration:none">BMS Digital Solutions</a> &middot; Salih Batanovic</p>
  <div class="footer-links">
    <a href="/login.php">Login</a>
    <a href="https://bmsdigitalsolutions.com/impressum.html" target="_blank">Impressum</a>
    <a href="https://bmsdigitalsolutions.com/datenschutz.html" target="_blank">Datenschutz</a>
  </div>
</footer>

<script>
var nav=document.getElementById('nav');
window.addEventListener('scroll',function(){nav.classList.toggle('scrolled',scrollY>30);},{passive:true});

var ro=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting)e.target.classList.add('visible');});
},{threshold:.12});
document.querySelectorAll('.reveal').forEach(function(el){ro.observe(el);});
</script>
</body>
</html>