<?php
// preview/templates/pages/kontakt.php

$seo  = $c['pages']['kontakt']['seo'] ?? [];
$secs = $c['pages']['kontakt']['sections'] ?? [];
$hero = $secs['hero']['data'] ?? [];
$info = $secs['contact']['data'] ?? [];
$site = $c['site'] ?? [];
$email = h($site['email'] ?? '');
$maps  = h($site['maps_url'] ?? '#');
$hours = h($info['hours'] ?? 'Mo–Fr: 7:00–21:00');
$hours_weekend = h($info['hours_weekend'] ?? 'Sa: 9:00–16:00');

echo render_head(h($seo['title'] ?? 'Kontakt | FlexFit Wien'), $seo);
echo render_navbar($c);
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px">Kontakt</span>
    <h1><?= h($info['headline'] ?? 'Lass uns reden') ?></h1>
    <p><?= h($info['subtext'] ?? 'Wir freuen uns auf deine Nachricht – und melden uns innerhalb von 24 Stunden.') ?></p>
  </div>
</section>

<section class="contact-section section-pad">
  <div class="container">
    <div class="contact-grid">
      <div>
        <span class="section-label">Infos &amp; Standort</span>
        <h2 class="section-title">Besuch uns in Wien 1080</h2>
        <div class="contact-info" style="margin-bottom:24px">
          <div class="contact-item">
            <div class="contact-item-icon">📍</div>
            <div>
              <div class="contact-item-label">Adresse</div>
              <div class="contact-item-value"><a href="<?= $maps ?>"><?= h($info['address'] ?? '') ?></a></div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">✉️</div>
            <div>
              <div class="contact-item-label">E-Mail</div>
              <div class="contact-item-value"><a href="mailto:<?= $email ?>"><?= $email ?></a></div>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-item-icon">🕐</div>
            <div>
              <div class="contact-item-label">Öffnungszeiten</div>
              <div class="contact-item-value"><?= $hours ?><br><span style="color:var(--text-secondary)"><?= $hours_weekend ?></span></div>
            </div>
          </div>
        </div>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2658.3!2d16.3508!3d48.2105!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x476d07c2e74cdca3%3A0x8f4ae0e76e4c4!2sMusterstraße+50%2C+1080+Wien!5e0!3m2!1sde!2sat!4v1"
          width="100%" height="240" style="border:0;border-radius:12px;display:block" allowfullscreen loading="lazy"></iframe>
      </div>
      <div class="contact-form-wrap">
        <h3 class="contact-form-title">Schreib uns direkt</h3>
        <p class="contact-form-sub">Oder buche dein <a href="/probetraining" style="color:var(--gold);font-weight:600">gratis Probetraining</a> online.</p>
        <form id="contactForm" method="POST" action="/api/contact">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Vorname *</label>
              <input class="form-input" name="firstname" type="text" placeholder="Max" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nachname *</label>
              <input class="form-input" name="lastname" type="text" placeholder="Mustermann" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">E-Mail *</label>
            <input class="form-input" name="email" type="email" placeholder="max@beispiel.at" required>
          </div>
          <div class="form-group">
            <label class="form-label">Interesse</label>
            <select class="form-select" name="interest">
              <option value="">Bitte wählen…</option>
              <option>Personal Training (1:1)</option>
              <option>Kleingruppentraining</option>
              <option>Firmenfitness</option>
              <option>Gratis Probetraining</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Nachricht</label>
            <textarea class="form-textarea" name="message" placeholder="Deine Nachricht…"></textarea>
          </div>
          <div class="form-submit-row">
            <p class="form-privacy"><a href="/datenschutz" style="color:var(--gold)">Datenschutz</a></p>
            <button type="submit" class="btn btn-primary">Absenden →</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php echo render_footer($c); ?>
