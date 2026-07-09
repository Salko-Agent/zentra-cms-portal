<?php
// preview/templates/pages/probetraining.php

$seo = $c['pages']['probetraining']['seo'] ?? [];

echo render_head(h($seo['title'] ?? 'Gratis Probetraining | FlexFit Wien'), $seo);
echo render_navbar($c);
?>

<section class="page-hero">
  <div class="container">
    <span class="section-label" style="justify-content:center;margin-bottom:16px">Kostenlos &amp; Unverbindlich</span>
    <h1>Gratis Probetraining<br>bei FlexFit Wien</h1>
    <p>Sichern Sie sich jetzt Ihr kostenloses, unverbindliches Probetraining (Gespr&auml;ch &amp; Training ca. 60 Min.) bei FlexFit Personal Training Wien.</p>
  </div>
</section>

<section class="section-pad" style="background:var(--off-white)">
  <div class="container">
    <div class="sg2-form">

      <!-- Formular (Position 1 – links / auf Mobile zuerst) -->
      <div class="contact-form-wrap" style="background:var(--white);box-shadow:var(--shadow-md);order:1">
        <h2 style="font-size:1.6rem;margin-bottom:8px">Probetraining anfragen</h2>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:28px">Wir melden uns innerhalb von 24 Stunden &ndash; kostenlos und unverbindlich.</p>

        <form id="probetrainingForm" novalidate>
          <input type="hidden" name="form_type" value="probetraining">

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
            <label class="form-label">Telefonnummer *</label>
            <input class="form-input" name="phone" type="tel" placeholder="+43 123 456 789" required>
          </div>

          <div class="form-group">
            <label class="form-label">Alter, Gr&ouml;&szlig;e / Gewicht, Ziele *</label>
            <input class="form-input" name="goal" type="text" placeholder="z.B. 35 Jahre, 175 cm, 85 kg &ndash; Abnehmen &amp; Kraft aufbauen" required>
          </div>

          <div class="form-group">
            <label class="form-label">M&ouml;gliche Trainingszeiten *</label>
            <input class="form-input" name="preferred_date" type="text" placeholder="z.B. Montag &amp; Mittwoch Abend, Samstag Vormittag" required>
          </div>

          <!-- Wunschtrainer mit Preisen -->
          <div class="form-group">
            <label class="form-label">Wunschtrainer *</label>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
              <?php
              $trainer_opts = [
                ['value'=>'Patrick','name'=>'Patrick K. Miller','focus'=>'Gesundheit, Fitness &amp; Lebensqualit&auml;t','price'=>'ab 86 &euro;','img'=>'/assets/img/team/trainer-1.jpg'],
                ['value'=>'Elias','name'=>'Elias Voggeneder','focus'=>'Muskelaufbau, Kraft &amp; Abnehmen','price'=>'ab 76 &euro;','img'=>'/assets/img/team/elias-voggeneder.jpeg'],
                ['value'=>'George','name'=>'Georgee P. Miller','focus'=>'Gesundheit, Krafttraining &amp; K&ouml;rperformung','price'=>'ab 69 &euro;','img'=>'/assets/img/team/trainer-2.jpg'],
              ];
              foreach ($trainer_opts as $tr): ?>
              <label class="trainer-radio-wrap" style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--off-white);border-radius:var(--r-md);border:2px solid var(--border-light);cursor:pointer;transition:border-color .2s">
                <input type="radio" name="trainer" value="<?= $tr['value'] ?>" required style="accent-color:var(--gold);width:18px;height:18px;flex-shrink:0">
                <img src="<?= $tr['img'] ?>" alt="<?= $tr['name'] ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;object-position:top;flex-shrink:0">
                <div>
                  <div style="font-weight:700;color:var(--text-primary)"><?= $tr['name'] ?></div>
                  <div style="font-size:.8rem;color:var(--text-muted)"><?= $tr['focus'] ?></div>
                  <div style="font-size:.82rem;color:var(--gold);font-weight:700;margin-top:2px"><?= $tr['price'] ?> / Training</div>
                </div>
              </label>
              <?php endforeach; ?>
              <label class="trainer-radio-wrap" style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--off-white);border-radius:var(--r-md);border:2px solid var(--border-light);cursor:pointer;transition:border-color .2s">
                <input type="radio" name="trainer" value="Trainer egal" style="accent-color:var(--gold);width:18px;height:18px;flex-shrink:0">
                <div style="width:48px;height:48px;border-radius:50%;background:var(--light-gray);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.3rem">&#x1F91D;</div>
                <div>
                  <div style="font-weight:700;color:var(--text-primary)">Trainer egal</div>
                  <div style="font-size:.8rem;color:var(--text-muted)">Wir empfehlen den passenden Trainer f&uuml;r Ihre Ziele</div>
                </div>
              </label>
            </div>
            <p style="font-size:.75rem;color:var(--text-muted);margin-top:8px">*Ab-Preise pro Einheit bei Kauf eines 10er-Blocks &agrave; 45 Minuten</p>
          </div>

          <div class="form-group">
            <label class="form-label">Weitere Infos (optional)</label>
            <textarea class="form-textarea" name="message" placeholder="Gesundheitliche Besonderheiten, fr&uuml;here Erfahrungen, Fragen&hellip;" style="min-height:90px"></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
            Probetraining sichern &rarr;
          </button>
          <p style="font-size:.75rem;color:var(--text-muted);text-align:center;margin-top:12px">
            Kostenlos &middot; Unverbindlich &middot; Kein Vertrag
          </p>
        </form>
      </div>

      <!-- Info (Position 2 – rechts) -->
      <div style="order:2">
        <span class="section-label" style="margin-bottom:20px;display:block">Was Sie erwartet</span>
        <h2 style="margin-bottom:24px;font-size:1.7rem">Der Weg zu mehr Lebensqualit&auml;t &amp; Fitness</h2>

        <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:40px">
          <?php
          $steps = [
            ['num'=>'01','title'=>'Kostenloses Erstgespr&auml;ch','text'=>'Wir besprechen Ihre Ziele, Ihren Gesundheitszustand und Ihr aktuelles Fitnesslevel. Keine Verpflichtungen.'],
            ['num'=>'02','title'=>'Bedarfsanalyse','text'=>'Ihr Trainer analysiert Ihre Bewegungsqualit&auml;t, St&auml;rken und eventuelle Einschr&auml;nkungen.'],
            ['num'=>'03','title'=>'Probetrainingseinheit','text'=>'Sie absolvieren eine vollst&auml;ndige Trainingseinheit &ndash; ma&szlig;geschneidert auf Sie. So wissen Sie sofort, wie es ist.'],
            ['num'=>'04','title'=>'Individuelle Empfehlung','text'=>'Am Ende erhalten Sie eine klare Empfehlung, welches Format am besten zu Ihnen und Ihren Zielen passt.'],
          ];
          foreach ($steps as $s): ?>
          <div style="display:flex;gap:18px;padding:18px 20px;background:var(--white);border-radius:var(--r-md);border:1px solid var(--border-light)">
            <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:700;color:var(--gold);line-height:1;min-width:44px"><?= $s['num'] ?></div>
            <div>
              <div style="font-weight:700;margin-bottom:4px"><?= $s['title'] ?></div>
              <div style="font-size:.88rem;color:var(--text-secondary)"><?= $s['text'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Studio Info -->
        <div style="padding:20px;background:var(--gold-subtle);border:1px solid rgba(200,150,60,.3);border-radius:var(--r-md)">
          <p style="font-size:.9rem;color:var(--text-primary);font-weight:500;margin:0">
            &#x1F4CD; <strong>Unser Studio:</strong> Musterstraße 12, 1010 Wien<br>
            &#x1F550; <strong>&Ouml;ffnungszeiten:</strong> Mo&ndash;Fr 7:00&ndash;21:00, Sa 9:00&ndash;16:00<br>
            &#x1F4DE; <strong>Telefon:</strong> <a href="tel:+43123456700" style="color:var(--text-primary)">+43 1 234567-00</a>
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
document.querySelectorAll('.trainer-radio-wrap input[type="radio"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.trainer-radio-wrap').forEach(function(w) { w.style.borderColor = ''; });
    if (radio.checked) radio.closest('.trainer-radio-wrap').style.borderColor = 'var(--gold)';
  });
});
</script>

<?php echo render_footer($c); ?>
