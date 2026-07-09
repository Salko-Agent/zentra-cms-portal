"""
Generates sync_flexfit.sql
Source of truth:
  - HOME page  → Flexfit-Website-UPLOAD/index.php (CMS-driven via content.json)
  - Other pages → Flexfit-Website-UPLOAD PHP files (hardcoded text = what the site shows)
"""
import json, sys
sys.stdout.reconfigure(encoding='utf-8')

def sq(s):
    """Escape for SQL single-quoted string."""
    return str(s).replace("'", "''")

def field_row(key, val, ftype='text'):
    return f"    UNION ALL SELECT '{sq(key)}', '{sq(val)}', '{ftype}'"

def first_field_row(key, val, ftype='text'):
    return f"    SELECT '{sq(key)}' AS fkey, '{sq(val)}' AS fval, '{ftype}' AS ftype"

def item_row(ord_num, obj):
    return f"    UNION ALL SELECT {ord_num}, '{sq(json.dumps(obj, ensure_ascii=False))}'"

def first_item_row(obj):
    return f"    SELECT 1 AS ord, '{sq(json.dumps(obj, ensure_ascii=False))}' AS json"

def fields_block(page_slug, sec_key, rows):
    """rows: list of (key, val, type)"""
    lines = []
    lines.append("INSERT INTO section_fields (section_id, field_key, field_value, field_type)")
    lines.append("SELECT sec.id, f.fkey, f.fval, f.ftype")
    lines.append("FROM sections sec")
    lines.append("  JOIN pages p ON sec.page_id = p.id")
    lines.append("  JOIN projects pr ON p.project_id = pr.id")
    lines.append("  CROSS JOIN (")
    k, v, t = rows[0]
    lines.append(f"    SELECT '{sq(k)}' AS fkey, '{sq(v)}' AS fval, '{t}' AS ftype")
    for k, v, t in rows[1:]:
        lines.append(f"    UNION ALL SELECT '{sq(k)}', '{sq(v)}', '{t}'")
    lines.append("  ) f")
    lines.append(f"WHERE pr.project_key = 'flexfit' AND p.slug = '{page_slug}' AND sec.section_key = '{sec_key}'")
    lines.append("ON DUPLICATE KEY UPDATE field_value = VALUES(field_value);")
    return "\n".join(lines)

def items_delete(page_slug, sec_key):
    return f"""DELETE si FROM section_items si
  JOIN sections sec ON si.section_id = sec.id
  JOIN pages p      ON sec.page_id   = p.id
  JOIN projects pr  ON p.project_id  = pr.id
WHERE pr.project_key = 'flexfit' AND p.slug = '{page_slug}' AND sec.section_key = '{sec_key}';"""

def items_insert(page_slug, sec_key, items):
    lines = []
    lines.append("INSERT INTO section_items (section_id, sort_order, item_json)")
    lines.append("SELECT sec.id, itm.ord, itm.json")
    lines.append("FROM sections sec")
    lines.append("  JOIN pages p ON sec.page_id = p.id")
    lines.append("  JOIN projects pr ON p.project_id = pr.id")
    lines.append("  CROSS JOIN (")
    j = json.dumps(items[0], ensure_ascii=False)
    lines.append(f"    SELECT 1 AS ord, '{sq(j)}' AS json")
    for i, item in enumerate(items[1:], 2):
        j = json.dumps(item, ensure_ascii=False)
        lines.append(f"    UNION ALL SELECT {i}, '{sq(j)}'")
    lines.append("  ) itm")
    lines.append(f"WHERE pr.project_key = 'flexfit' AND p.slug = '{page_slug}' AND sec.section_key = '{sec_key}';")
    return "\n".join(lines)

def section(title):
    bar = "=" * 60
    return f"\n-- {bar}\n-- {title}\n-- {bar}"


out = []
out.append("-- " + "=" * 60)
out.append("-- Zentra - FlexFit FULL CONTENT SYNC")
out.append("-- Quelle: Flexfit-Website-UPLOAD (echte Live-Seiten)")
out.append("-- phpMyAdmin -> your_database_name")
out.append("-- Nach Import: Einstellungen -> Jetzt synchronisieren")
out.append("-- " + "=" * 60)
out.append("SET NAMES utf8mb4;")

# ============================================================
# HOME / HERO  (index.php – all fields used by PHP template)
# trust_text default in PHP: '<strong>4.9/5</strong> – 65 Google-Bewertungen'
# bg_image: local path (used by JS for hero fallback)
# ============================================================
out.append(section("HOME / HERO"))
out.append(fields_block("home", "hero", [
    ("badge",               "Personal Training Wien · 1080",                      "text"),
    ("headline_line1",      "Personal Trainer Wien",                               "text"),
    ("headline_line2",      "FlexFit",                                            "text"),
    ("subheadline",         "Personal Training in Wien – mit Sportwissenschafter Patrick K. Miller & Team", "textarea"),
    ("price_note",          "Personal Training bereits ab 69 € / Einheit",         "text"),
    ("cta_primary",         "Gratis Probetraining buchen",                          "text"),
    ("cta_primary_url",     "/probetraining.php",                                   "url"),
    ("cta_secondary",       "Leistungen entdecken",                                 "text"),
    ("cta_secondary_url",   "#leistungen",                                          "url"),
    ("trust_text",          "<strong>4.9/5</strong> – 65 Google-Bewertungen",       "text"),
    ("bg_image",            "/assets/img/team/trainer-1.jpg",                  "url"),
]))

# ============================================================
# HOME / STATS  (index.php stats-strip)
# 4.9 WITHOUT star – star is rendered as HTML in PHP, not in data
# ============================================================
out.append(section("HOME / STATS"))
out.append(items_delete("home", "stats"))
out.append(items_insert("home", "stats", [
    {"number": "15+",  "label": "Jahre Erfahrung"},
    {"number": "65",   "label": "Google-Bewertungen"},
    {"number": "4.9",  "label": "Google Rating"},
    {"number": "69€",  "label": "ab / Einheit"},
]))

# ============================================================
# HOME / PARTNERS  (index.php – label hardcoded in PHP as "Zusammenarbeit mit")
# ============================================================
out.append(section("HOME / PARTNERS"))
out.append(fields_block("home", "partners", [
    ("label", "Zusammenarbeit mit", "text"),
]))
out.append(items_delete("home", "partners"))
out.append(items_insert("home", "partners", [
    {"name": "ORF",       "logo": "/assets/img/partners/orf.png"},
    {"name": "Waterdrop", "logo": "/assets/img/partners/waterdrop.png"},
    {"name": "Heute",     "logo": "/assets/img/partners/heute.png"},
    {"name": "Netdoktor", "logo": "/assets/img/partners/netdoktor.png"},
    {"name": "Gerngross", "logo": "/assets/img/partners/gerngross.png"},
    {"name": "Qualiant",  "logo": "/assets/img/partners/qualiant.png"},
]))

# ============================================================
# HOME / WHY  (index.php – label/headline/subtext/image/goals/features)
# label default in PHP: "Warum Personal Training bei FlexFit?"
# headline default:  "Individuelles Personal Training Wien –\nEndlich fit und schmerzfrei das Leben genießen"
# subtext default:   "Sie haben überlaufene Fitness Studios satt..."
# ============================================================
out.append(section("HOME / WHY"))
out.append(fields_block("home", "why", [
    ("label",               "Warum Personal Training bei FlexFit?", "text"),
    ("headline",            "Individuelles Personal Training Wien –\nEndlich fit und schmerzfrei das Leben genießen", "text"),
    ("subtext",             "Sie haben überlaufene Fitness Studios satt und möchten endlich mit individueller Betreuung Ihre Fitness-Ziele erreichen? Dann sind Sie bei FlexFit genau richtig.", "textarea"),
    ("image",               "/assets/img/hero/ball-curls.png",    "url"),
    ("image_badge_number",  "15+",                                "text"),
    ("image_badge_label",   "Jahre Erfahrung",                    "text"),
    ("goals",               json.dumps(["Abnehmen","Muskelaufbau","Rücken","Gesundheit","Langlebigkeit","Fitness"], ensure_ascii=False), "text"),
]))
out.append(items_delete("home", "why"))
out.append(items_insert("home", "why", [
    {"icon": "🎯",  "title": "Maßgeschneidertes Training",     "text": "Kein Einheitsplan – jede Einheit wird individuell auf deine Ziele, dein Fitnesslevel und deinen Gesundheitszustand abgestimmt."},
    {"icon": "🏛️", "title": "Privates Studio – kein Gedränge","text": "Trainiere in unserem voll ausgestatteten Privatstudio in der Musterstraße 12, 1010 Wien – stressfrei und ungestört."},
    {"icon": "🔬",  "title": "Wissenschaftlich fundiert",       "text": "Patrick K. Miller ist Sportwissenschaftler. Jedes Programm basiert auf aktueller Forschung – effizient, sicher, nachhaltig."},
    {"icon": "💡",  "title": "Mehr als Training",               "text": "Lifestyle-Coaching zu Ernährung, Schlaf und Stressmanagement – weil nachhaltiger Erfolg mehr als Gewichte heben braucht."},
]))

# ============================================================
# HOME / SERVICES  (index.php – items with local image paths)
# headline/label in index.php are HARDCODED ("Training, das zu dir passt")
# – we sync them anyway for CMS completeness
# ============================================================
out.append(section("HOME / SERVICES"))
out.append(fields_block("home", "services", [
    ("label",    "Leistungen",                "text"),
    ("headline", "Was wir anbieten",          "text"),
]))
out.append(items_delete("home", "services"))
out.append(items_insert("home", "services", [
    {
        "title": "Personal Training", "short": "1:1 Training",
        "description": "Individuelles, persönliches 1:1 Fitnesstraining für maßgeschneiderte, optimale Betreuung – alleine oder zu zweit.",
        "image": "/assets/img/training/sumo-squat.jpg",
        "url": "/personal-training.php", "price": "ab 69 €", "price_unit": "/ Einheit"
    },
    {
        "title": "Kleingruppentraining", "short": "2–4 Personen",
        "description": "Training mit Partner:in oder Freunden – für extra Motivation, mehr Spaß und dennoch persönliche Betreuung.",
        "image": "/assets/img/training/gruppentraining.jpg",
        "url": "/personal-training.php#kleingruppe", "price": "auf Anfrage", "price_unit": ""
    },
    {
        "title": "Firmenfitness", "short": "Corporate",
        "description": "Trainings und Workshops direkt in Ihrem Unternehmen – für fitte, gesunde und motivierte Mitarbeiter.",
        "image": "/assets/img/firmenfitness/hero.jpeg",
        "url": "/firmenfitness.php", "price": "auf Anfrage", "price_unit": ""
    },
]))

# ============================================================
# HOME / TRAINER  (index.php – Patrick's profile section)
# specs stored as section_items (transform aliases trainer.items -> specs)
# specializations stored as JSON string (it's in $json_fields, gets parsed)
# ============================================================
out.append(section("HOME / TRAINER (Patrick – Homepage-Profil)"))
out.append(fields_block("home", "trainer", [
    ("name",            "Patrick K. Miller",                                            "text"),
    ("title",           "Gründer & Head Personal Trainer",                           "text"),
    ("credentials",     "Sportwissenschaftler · Staatl. gepr. Fitnesstrainer · Autor","text"),
    ("experience",      "15+ Jahre Erfahrung",                                       "text"),
    ("bio",             "Als Sportwissenschaftler und staatlich geprüfter Fitnesstrainer mit über 15 Jahren Erfahrung habe ich FlexFit mit einer klaren Vision gegründet: Personal Training auf höchstem wissenschaftlichem Niveau – persönlich, effizient und nachhaltig.", "textarea"),
    ("bio2",            "Mein Ansatz verbindet aktuelle Sportwissenschaft mit individueller Betreuung. Das Ergebnis: echte Transformationen, nicht nur kurzfristige Erfolge.", "textarea"),
    ("photo",           "/assets/img/team/trainer-1.jpg",                       "url"),
    ("specializations", json.dumps(["Gesundheitsorientiertes Krafttraining & Langlebigkeit","Gesunder Rücken & Schmerzfreiheit","Muskelaufbau, Fettabbau & Straffung","Fitness & Lebensqualität","Training bei metabolischem Syndrom"], ensure_ascii=False), "text"),
]))
# specs as section_items → transform renames items→specs for 'trainer' section
out.append(items_delete("home", "trainer"))
out.append(items_insert("home", "trainer", [
    {"icon": "🎓", "label": "Ausbildung",      "value": "Sportwissenschaft (Uni)"},
    {"icon": "📜", "label": "Zertifizierung",  "value": "Staatl. gepr. Fitnesstrainer"},
    {"icon": "⏳", "label": "Erfahrung",        "value": "15+ Jahre Personal Training"},
    {"icon": "📖", "label": "Autor",            "value": "Fitness- & Gesundheitsbücher"},
]))

# ============================================================
# HOME / BENEFITS  (index.php – label/headline/subtext + items)
# ============================================================
out.append(section("HOME / BENEFITS"))
out.append(fields_block("home", "benefits", [
    ("label",    "Warum Krafttraining",                                                                      "text"),
    ("headline", "Krafttraining grenzt an ein Wundermittel",                                                 "text"),
    ("subtext",  "Aktuelle Studien belegen: regelmäßiges Krafttraining ist die effektivste Maßnahme für Gesundheit und Langlebigkeit.", "textarea"),
]))
out.append(items_delete("home", "benefits"))
out.append(items_insert("home", "benefits", [
    {"icon": "💪", "title": "Muskelaufbau & Stärke",       "text": "Baut Muskelmasse auf, macht stärker und verbessert die körperliche Leistungsfähigkeit nachhaltig."},
    {"icon": "❤️", "title": "Herz-Kreislauf-Gesundheit",  "text": "Wirkt positiv auf Herz, Kreislauf, Immunsystem, Gelenke, Knochen und sogar das Gehirn."},
    {"icon": "🦴",  "title": "Rücken & Gelenke",           "text": "Lindert oder eliminiert Rücken- und Nackenschmerzen – einer der häufigsten Gründe für Trainingsstart."},
    {"icon": "🧠",  "title": "Mentale Stärke",              "text": "Reduziert Stress, verbessert Schlaf und Konzentration. Sport ist das beste Antidepressivum."},
    {"icon": "🔥",  "title": "Fettabbau",                   "text": "Erhöht den Grundumsatz dauerhaft. Mehr Muskeln = mehr Kalorienverbrauch – auch im Ruhezustand."},
    {"icon": "⏳",  "title": "Gesund altern",               "text": "Prävention gegen Adipositas, Bluthochdruck, Diabetes, Demenz und Gebrechlichkeit im Alter."},
]))

# ============================================================
# HOME / TESTIMONIALS  (index.php – items only; header is hardcoded)
# ============================================================
out.append(section("HOME / TESTIMONIALS"))
out.append(fields_block("home", "testimonials", [
    ("label",    "Was Kunden sagen",             "text"),
    ("headline", "Echte Ergebnisse, echte Menschen", "text"),
]))
out.append(items_delete("home", "testimonials"))
out.append(items_insert("home", "testimonials", [
    {"name": "Peter K. Miller",  "text": "Bin erst seit ca. 6 Wochen bei Patrick in Training und kann schon die ersten Erfolge sehen und spüren! Das Training ist perfekt auf meine Bedürfnisse und Ziele abgestimmt! Absolute Empfehlung 💪", "rating": 5, "date": "September 2025", "initials": "PK"},
    {"name": "Roman W. Miller",  "text": "Top, top, top! FlexFit mit Patrick hat alles was es für ein geführtes Training braucht: einen exzellenten Trainer mit langjähriger Erfahrung, einen guten Mix aus Forderung und Förderung. Ich trainiere seit 4 Monaten und noch nie ist soviel weitergegangen – ohne Stress und Hektik!", "rating": 5, "date": "Juni 2025", "initials": "RW"},
    {"name": "Markus Pusnik",  "text": "Absolut begeistert: Kompetent, individuell fordernd und motivierend! Trainings werden mit meinem Physio akkordiert und ich bekomme auch Pläne fürs Heimtraining. Die Location ist äußerst ansprechend und super clean! Klare Empfehlung.", "rating": 5, "date": "Juni 2025", "initials": "MP"},
]))

# ============================================================
# PERSONAL TRAINING / HERO  (from live personal-training.php)
# ============================================================
out.append(section("PERSONAL TRAINING / HERO"))
out.append(fields_block("personal_training", "hero", [
    ("label",             "Personal Training Wien",                    "text"),
    ("headline",          "Training bei FlexFit",                    "text"),
    ("subheadline",       "Endlich fit und schmerzfrei das Leben genießen", "text"),
    ("subtext",           "Sie haben überlaufene Fitness Studios satt und möchten endlich mit individueller Betreuung Ihre Fitness-Ziele erreichen? Dann sind Sie bei FlexFit genau richtig.", "textarea"),
    ("note",              "Krafttraining ist nachweislich das wichtigste Training für Ihre Gesundheit", "text"),
    ("bg_image",          "/assets/img/hero/ball-curls.png",           "url"),
    ("cta_primary",       "Gratis Probetraining buchen",               "text"),
    ("cta_primary_url",   "/probetraining.php",                        "url"),
    ("cta_secondary",     "Preise ansehen",                            "text"),
    ("cta_secondary_url", "#preise",                                   "url"),
]))

# ============================================================
# PERSONAL TRAINING / REASONS  (from live personal-training.php $reasons array)
# ============================================================
out.append(section("PERSONAL TRAINING / REASONS"))
out.append(fields_block("personal_training", "reasons", [
    ("label",    "Warum Personal Training bei FlexFit?", "text"),
    ("headline", "Individuelles Personal Training Wien – der Unterschied zwischen Wollen und Erreichen", "text"),
    ("subtext",  "Ein persönlicher Coach kann den entscheidenden Unterschied zwischen mittelmäßigen Ergebnissen und außergewöhnlichem Erfolg ausmachen.", "textarea"),
    ("image",    "/assets/img/training/collage.jpeg",   "url"),
]))
out.append(items_delete("personal_training", "reasons"))
out.append(items_insert("personal_training", "reasons", [
    {"icon": "🎯",  "title": "Maßgeschneiderte Trainingspläne", "text": "Unsere erfahrenen Personal Coaches berücksichtigen Ihre Ziele, Fitnesslevel, Gesundheitszustand und persönlichen Vorlieben, um einen individuellen Trainingsplan zu erstellen, der perfekt zu Ihnen passt."},
    {"icon": "💪",  "title": "Motivation & Unterstützung",      "text": "Mit einem erfahrenen, engagierten Fitnessexperten an Ihrer Seite werden Sie kontinuierlich motiviert und herausgefordert, Ihr Bestes zu geben. Ihr Trainer hält Sie auf Kurs, selbst wenn die Motivation nachlässt."},
    {"icon": "🛡️", "title": "Verletzungsprävention",           "text": "Unsere Personal Coaches achten besonders auf die richtige Ausführung der Übungen, um Verletzungen zu vermeiden und die Effektivität Ihrer Workouts zu maximieren."},
    {"icon": "⚡",  "title": "Effizienz & Zeitersparnis",        "text": "In nur einer Einheit erreichen Sie oft das, wofür Sie im Fitnessstudio zwei Einheiten benötigen würden – dank intensiver Supersätze. So genügen bereits 1–2 Einheiten pro Woche."},
    {"icon": "🧬",  "title": "Lifestyle-Coaching",               "text": "Unsere Trainer helfen Ihnen, Ihren Alltag besser zu bewältigen, mit zusätzlicher Betreuung zu Ernährung, Bewegung, Schlaf & Stressmanagement."},
    {"icon": "🔬",  "title": "Wissenschaftlich fundiert",        "text": "Patrick ist Sportwissenschaftler. Alle Methoden basieren auf aktueller Forschung – nicht auf Mythen."},
]))

# ============================================================
# PERSONAL TRAINING / FORMATS  (from live personal-training.php pricing)
# Actual prices: Patrick ab 86€, Elias ab 76€, George ab 69€
# Group: 2×60€, 3×50€, 4×40€ p.P.
# ============================================================
out.append(section("PERSONAL TRAINING / FORMATS (Preise)"))
out.append(fields_block("personal_training", "formats", [
    ("label",    "Trainingsformate & Preise",            "text"),
    ("headline", "Personal Training bereits ab 69 € / Einheit", "text"),
    ("footnote", "Ab-Preise bei Kauf eines 45 min. 10er-Blocks.", "text"),
]))
out.append(items_delete("personal_training", "formats"))
out.append(items_insert("personal_training", "formats", [
    {"name": "Patrick K. Miller",   "focus": "Gesundheit, Fitness & Lebensqualität",    "price": "ab 86 €", "price_unit": "/ Einheit", "featured": True,  "img": "/assets/img/team/trainer-1.jpg",   "specs": ["Sportwissenschaftler", "15+ Jahre Erfahrung", "Rücken & Langlebigkeit"]},
    {"name": "Elias Voggeneder", "focus": "Muskelaufbau, Kraft & Abnehmen",           "price": "ab 76 €", "price_unit": "/ Einheit", "featured": False, "img": "/assets/img/team/elias-voggeneder.jpeg","specs": ["Staatl. gepr. Fitnesstrainer", "Kraft & Kondition"]},
    {"name": "Georgee P. Miller",       "focus": "Gesundheit, Krafttraining & Körperformung","price": "ab 69 €", "price_unit": "/ Einheit", "featured": False, "img": "/assets/img/team/trainer-2.jpg",      "specs": ["Staatl. gepr. Fitnesstrainer", "Gesundheit & Rehabilitation"]},
]))

# ============================================================
# PERSONAL TRAINING / CTA  (from live personal-training.php cta)
# ============================================================
out.append(section("PERSONAL TRAINING / CTA"))
out.append(fields_block("personal_training", "cta", [
    ("headline",       "Starten Sie heute – kostenlos & unverbindlich",                                                               "text"),
    ("subtext",        "Buchen Sie Ihr gratis Probetraining und erleben Sie selbst, wie FlexFit Sie zu Ihren Zielen bringt.", "textarea"),
    ("btn_primary",    "Gratis Probetraining buchen →",                                                                               "text"),
    ("btn_secondary",  "Per E-Mail anfragen",                                                                                          "text"),
    ("note",           "✓ Kostenlos · ✓ Unverbindlich · ✓ Kein Vertrag",                                                              "text"),
]))

# ============================================================
# TEAM / HERO  (from live team.php – hardcoded)
# ============================================================
out.append(section("TEAM / HERO"))
out.append(fields_block("team", "hero", [
    ("label",    "Das Team",                                                                                                               "text"),
    ("headline", "Experten für\ndeine Gesundheit",                                                                                         "text"),
    ("subtext",  "Wir sind stets bemüht, Ihnen durch enge Zusammenarbeit unseres Teams eine optimale Betreuung zu gewähren.", "textarea"),
]))

# ============================================================
# TEAM / TRAINERS  (from live team.php – hardcoded trainer cards)
# ============================================================
out.append(section("TEAM / TRAINERS"))
out.append(fields_block("team", "trainers", [
    ("label",    "Personal Training", "text"),
    ("headline", "Unsere Trainer",    "text"),
]))
out.append(items_delete("team", "trainers"))
out.append(items_insert("team", "trainers", [
    {
        "name":  "Patrick K. Miller",
        "title": "Gründer & Head Personal Trainer",
        "credentials": "Sportwissenschaftler · Staatl. gepr. Fitnesstrainer · Autor",
        "photo": "/assets/img/team/trainer-1.jpg",
        "bio":   "Profitieren Sie von über 15 Jahren Erfahrung als Personal Trainer.",
        "bio_quote": "Mein Ziel ist es die wunderbaren Auswirkungen eines bewussten Lebensstils meinen Mitmenschen zu vermitteln und so auch zu motivieren. Es macht mich sehr glücklich Menschen dabei zu helfen ihre Fitness & Gesundheit nachhaltig zu verbessern.",
        "specializations": ["Allgemeine Fitness & Lifestyle Coaching", "Muskelaufbau & Fettreduktion", "Rückenschmerzen & Fit im Alter"],
        "ausbildung": ["Studium Sportwissenschaft", "Staatlich geprüfter Fitnesstrainer (BSPA Linz)", "ESP Wirbelsäulenrehabilitation", "Über 15 Jahre & 10.000 Trainings Erfahrung"],
    },
    {
        "name":  "Elias Voggeneder",
        "title": "Personal Trainer",
        "credentials": "Staatl. gepr. Fitnesstrainer",
        "photo": "/assets/img/team/elias-voggeneder.jpeg",
        "bio":   "Möchten Sie Muskeln aufbauen, fitter werden und sich rundum wohlfühlen? Ich helfe Ihnen dabei, Ihren inneren Schweinehund zu überwinden und begleite Sie auf dem Weg zu Ihrem persönlichen Fitnessziel.",
        "bio2":  "Meine Leidenschaft für den Sport begann schon in jungen Jahren und führte dazu, dass ich mein Hobby zum Beruf machte.",
        "specializations": ["Krafttraining & Trainingsplanung", "Ausdauer & Kondition", "Ernährungscoaching"],
        "ausbildung": ["Lehre als Fitnessbetreuer (mit Auszeichnung)", "Masterclass of Personal Training 1-3", "Online Strength Coach", "8 Jahre Erfahrung & über 300 zufriedene Kunden"],
    },
    {
        "name":  "Georgee P. Miller",
        "title": "Personal Trainer",
        "credentials": "Staatl. gepr. Fitnesstrainer",
        "photo": "/assets/img/team/trainer-2.jpg",
        "bio":   "George verstärkt das FlexFit-Team mit umfassender Expertise im Bereich Kraft- und Gesundheitstraining.",
        "bio2":  "Ganzheitliche Betreuung für nachhaltige Schmerzfreiheit und Leistungssteigerung individuell abgestimmt auf Ihre Bedürfnisse.",
        "specializations": ["Gesundheitstraining & Prävention", "Rehabilitation & Beweglichkeit", "Krafttraining & Muskelaufbau"],
        "ausbildung": ["Staatlich geprüfter Fitnesstrainer", "Spezialisiert auf gesundheitsorientiertes Krafttraining"],
    },
]))

# ============================================================
# TEAM / PHYSIO  (from live team.php – hardcoded, phone numbers are facts)
# ============================================================
out.append(section("TEAM / PHYSIO"))
out.append(fields_block("team", "physio", [
    ("label",       "Physiotherapie",                                                       "text"),
    ("headline",    "Unser Physio-Team",                                                    "text"),
    ("subtext",     "Kontaktieren Sie den Therapeuten Ihrer Wahl direkt für einen Termin.", "textarea"),
    ("link_label",  "→ Mehr zur Physiotherapie bei FlexFit",                              "text"),
    ("link_url",    "/physiotherapie.php",                                                  "url"),
]))
out.append(items_delete("team", "physio"))
out.append(items_insert("team", "physio", [
    {"name": "Ivo Österreicher", "phone": "+43 1 234567-01",  "phone_display": "+43 660 507 8578"},
    {"name": "Lisa M. Becker",  "phone": "+43 1 234567-02", "phone_display": "+43 677 617 365 10"},
    {"name": "Gregor W. Weber",     "phone": "+43 1 234567-03",  "phone_display": "+43 680 204 2401"},
    {"name": "Jaron I. Fischer",        "phone": "+43 1 234567-04",  "phone_display": "+43 660 745 4198"},
]))

# ============================================================
# TEAM / CTA  (from live team.php – hardcoded cta banner)
# ============================================================
out.append(section("TEAM / CTA"))
out.append(fields_block("team", "cta", [
    ("headline",       "Lerne unser Team kennen",                                              "text"),
    ("subtext",        "Buche dein kostenloses Probetraining und triff Patrick persönlich.", "textarea"),
    ("btn_primary",    "Probetraining buchen →",                                              "text"),
    ("btn_secondary",  "Kontakt aufnehmen",                                                   "text"),
]))

# ============================================================
# PERSONAL TRAINING / KFT  (from live personal-training.php $kft array)
# "Deshalb Krafttraining" benefits section
# ============================================================
out.append(section("PERSONAL TRAINING / KFT (Deshalb Krafttraining)"))
out.append(fields_block("personal_training", "kft", [
    ("label",    "Deshalb Krafttraining",                                                                          "text"),
    ("headline", "Krafttraining grenzt an ein Wundermittel",                                                       "text"),
    ("subtext",  "Wie aktuelle Studien bestätigen – Krafttraining ist das wichtigste Training für Ihre Gesundheit", "textarea"),
]))
out.append(items_delete("personal_training", "kft"))
out.append(items_insert("personal_training", "kft", [
    {"icon": "💪", "title": "Muskeln aufbauen",    "text": "Krafttraining baut Muskeln auf & macht stärker – der wichtigste Schutz gegen altersbedingten Muskelschwund."},
    {"icon": "❤️", "title": "Herz & Immunsystem",  "text": "Wirkt positiv auf Immunsystem, Herz-Kreislauf-System, Gelenke, Knochen & Gehirn."},
    {"icon": "🦴",  "title": "Rücken & Nacken",    "text": "Lindert oder eliminiert Rücken- & Nackenschmerzen durch gezieltes Wirbelsäulentraining."},
    {"icon": "🩺",  "title": "Prävention",          "text": "Prävention gegen Adipositas, Bluthochdruck, Diabetes, Demenz und viele weitere Erkrankungen."},
    {"icon": "⏳",  "title": "Fit im Alter",        "text": "Krafttraining ist extrem wichtig für Fitness, Selbständigkeit & Lebensqualität im Alter."},
    {"icon": "🌟",  "title": "Lebensqualität",      "text": "Insgesamt erfahren Sie durch individuelles Krafttraining einen deutlichen Anstieg an Lebensqualität."},
]))

# Also add quote + group pricing fields to personal_training/reasons and formats
out.append(section("PERSONAL TRAINING / REASONS (extra fields: quote)"))
out.append(fields_block("personal_training", "reasons", [
    ("quote",        "Unser Training ist ideal für vielbeschäftigte Menschen, denn in nur einer Einheit erreichen Sie oft das, wofür Sie im Fitnessstudio zwei Einheiten benötigen würden, da wir mit intensiven Supersätzen arbeiten, was in Fitness Studios nicht möglich ist.", "textarea"),
    ("quote_author", "— Patrick K. Miller, Geschäftsführer FlexFit Personal Training e.U.",                                                                                                                                                                                       "text"),
]))

out.append(section("PERSONAL TRAINING / FORMATS (extra fields: group pricing)"))
out.append(fields_block("personal_training", "formats", [
    ("group_headline", "Gruppentraining – ab 40 € / Person",                                                                           "text"),
    ("group_subtext",  "Sie trainieren lieber in der Gruppe? Motivieren Sie Ihre Freund:innen und profitieren Sie von attraktiven Gruppenangeboten!", "textarea"),
    ("group_items",    json.dumps(["2 Personen: 60 € p.P.", "3 Personen: 50 € p.P.", "4 Personen: 40 € p.P."], ensure_ascii=False),     "text"),
]))

# ============================================================
# PHYSIOTHERAPIE / HERO
# ============================================================
out.append(section("PHYSIOTHERAPIE / HERO"))
out.append(fields_block("physiotherapie", "hero", [
    ("label",               "Physiotherapie 1080 Wien",                          "text"),
    ("headline",            "Ihre Gesundheit\nim Fokus",                         "text"),
    ("subtext",             "Wir verbinden passive und aktive Physiotherapie. Musterstraße 12, 1010 Wien.", "textarea"),
    ("bg_image",            "/assets/img/physio/physio-hero.jpg",                "url"),
    ("cta_primary",         "Termin anfragen",                                   "text"),
    ("cta_primary_url",     "#team",                                             "url"),
    ("cta_secondary",       "Leistungen ansehen",                                "text"),
    ("cta_secondary_url",   "#leistungen",                                       "url"),
]))

# ============================================================
# PHYSIOTHERAPIE / TREATMENTS
# ============================================================
out.append(section("PHYSIOTHERAPIE / TREATMENTS"))
out.append(fields_block("physiotherapie", "treatments", [
    ("label",    "Leistungen",                   "text"),
    ("headline", "Was wir behandeln",            "text"),
    ("subtext",  "Wir bieten unseren Patienten nicht nur Linderung ihrer Beschwerden, sondern ermöglichen ihnen auch eine nachhaltige Steigerung ihrer Lebensqualität.", "textarea"),
    ("image",    "/assets/img/physio/physio-hero.jpg", "url"),
]))
out.append(items_delete("physiotherapie", "treatments"))
out.append(items_insert("physiotherapie", "treatments", [
    {"text": "Sportphysiotherapie & Trainingstherapie"},
    {"text": "Aktive & passive Reha"},
    {"text": "Behandlung bei orthopädischen und traumatologischen Beschwerden"},
    {"text": "Postoperative Reha"},
    {"text": "Return to Sport"},
]))

# ============================================================
# PHYSIOTHERAPIE / TEAM  (full bios – physio therapists)
# ============================================================
out.append(section("PHYSIOTHERAPIE / TEAM"))
out.append(fields_block("physiotherapie", "team", [
    ("label",    "Unser Team",                                                               "text"),
    ("headline", "Ihre Physiotherapeut:innen",                                               "text"),
    ("subtext",  "Klicken Sie auf die Telefonnummer des Therapeuten Ihrer Wahl, um einen Termin zu vereinbaren.", "textarea"),
]))
out.append(items_delete("physiotherapie", "team"))
out.append(items_insert("physiotherapie", "team", [
    {
        "name": "Ivo Österreicher", "initials": "IO", "phone": "+43 1 234567-01",
        "bio":  "Karrierethemen, Berufsstress und Schmerzen sind die großen Themen unseres Alltags. Eine allgemeine Überforderung spiegelt sich oft in körperlichem Unwohlsein wider. Dahingehend liegt es mir am Herzen, PatientInnen zu Bewegung zu motivieren.",
        "bio2": "Ich begleite auch SportlerInnen in der Akutphase bis zum Wiedereinstieg (Praktikum SK Rapid Wien II).",
        "specs_label": "Spezialisierungen:",
        "specs": ["Wirbelsäule: Bandscheibenvorfall, Facettengelenksarthrose, ISG-Problematiken", "Schulter: Subakromiales Schmerzsyndrom, Rotatorenmanschette, Frozen Shoulder", "Knie: Kreuzbandriss, Meniskus, Patellafemorales Schmerzsyndrom"],
        "ausbildung": ["BSc. Physiotherapie (FH Campus Wien)", "ESP Sportphysiotherapie Level 1 & 2", "The Painful Shoulder (Adam Meakins)"],
    },
    {
        "name": "Lisa M. Becker", "initials": "LM", "phone": "+43 1 234567-02",
        "bio":  "Egal ob es darum geht, Verletzungen vorzubeugen oder die Rehabilitation zu unterstützen – ich stehe Ihnen mit meinem Fachwissen zur Seite. Als ehemalige Leistungssportlerin im Volleyball setze ich auf eine ganzheitliche Herangehensweise.",
        "bio2": "Mein Ziel ist es, dass sich meine Patient*Innen wohl und durch einen individuellen Therapieplan optimal betreut fühlen.",
        "specs_label": "Spezialisierungen:",
        "specs": ["Knie: Kniearthrose, Kreuzbandruptur, Runners/Jumpers Knee", "Schulter: Impingement, Frozen Shoulder, Instabilität", "Wirbelsäule: Bandscheibenvorfall, Hexenschuss, Cervicalsyndrom"],
        "ausbildung": ["BSc. Physiotherapie (FH Wien)", "ESP Sportphysiotherapie Level 1 & 2", "ESP Medizinische Aktive Reha", "Assessments im Breitensport"],
    },
    {
        "name": "Gregor W. Weber", "initials": "GW", "phone": "+43 1 234567-03",
        "bio":  "Schon seit meinen Kindheitstagen hat Sport einen sehr hohen Stellenwert. Vor allem die Individualität am Arbeiten mit Menschen macht mir große Freude.",
        "bio2": "Meine Philosophie ist es, Patienten durch umfangreiche Aufklärung und evidenzbasierte Therapiemethoden langfristige Lösungsansätze für Beschwerden am Bewegungsapparat zu vermitteln.",
        "specs_label": "Schwerpunkte:",
        "specs": ["Orthopädische & traumatologische Beschwerden: Wirbelsäule, Schulter, Knie, Sprunggelenk", "Postoperative Begleitung & Überlastungsbeschwerden", "Return to Sport / Präventive Physiotherapie"],
        "ausbildung": ["BSc. Physiotherapie (FH Campus Wien)", "ESP Sportphysiotherapie Level 1 & 2", "The Painful Shoulder (Adam Meakins)", "Rehaletik & Sport-Assessments"],
    },
    {
        "name": "Jaron I. Fischer", "initials": "JI", "phone": "+43 1 234567-04",
        "bio":  "Die Faszination für den menschlichen Körper begleitet mich schon seit meiner Kindheit. Es ist mir ein Anliegen, dass Sie als Mensch im Mittelpunkt der Therapie stehen.",
        "bio2": "Um Ihre Beschwerden nachhaltig zu behandeln, verfolge ich einen aktiven und evidenzbasierten Ansatz, der aktuelle Forschung mit manuellen Techniken und therapeutischen Übungen verbindet.",
        "specs_label": "Schwerpunkte:",
        "specs": ["Wirbelsäule, Schulter, Hüfte, Knie", "Sportverletzungen", "Osteoporose"],
        "ausbildung": ["MSc. (CE) Sportphysiotherapie (i.A.)", "Krafttraining für Physiotherapeuten", "The Shoulder (Jared Powell & Adam Meakins)", "Biomechanisches Know-How (A. Pürzel)"],
    },
]))

# ============================================================
# PHYSIOTHERAPIE / PREISE
# ============================================================
out.append(section("PHYSIOTHERAPIE / PREISE"))
out.append(fields_block("physiotherapie", "preise", [
    ("label",    "Preise",                    "text"),
    ("headline", "Transparente Kosten",       "text"),
    ("footnote", "Für die Behandlung benötigen Sie eine ärztliche Verordnung. Krankenkassen-Rückerstattung möglich.", "textarea"),
]))
out.append(items_delete("physiotherapie", "preise"))
out.append(items_insert("physiotherapie", "preise", [
    {"duration": "Physiotherapie 45 min", "price": "€ 90,-"},
    {"duration": "Physiotherapie 60 min", "price": "€ 110,-"},
]))

# ============================================================
# PHYSIOTHERAPIE / ABLAUF
# ============================================================
out.append(section("PHYSIOTHERAPIE / ABLAUF"))
out.append(fields_block("physiotherapie", "ablauf", [
    ("label",    "Ablauf",                              "text"),
    ("headline", "So läuft eine Behandlung ab",         "text"),
]))
out.append(items_delete("physiotherapie", "ablauf"))
out.append(items_insert("physiotherapie", "ablauf", [
    {"step": "01", "text": "Ärztliche Verordnung zur Physiotherapie von Ihrem praktischen Arzt oder Facharzt besorgen."},
    {"step": "02", "text": "Spätestens vor der 2. Behandlung chefärztliche Bewilligung bei Ihrer Krankenkasse einholen (ÖGK derzeit bewilligungsfrei)."},
    {"step": "03", "text": "Behandlungskosten werden in Absprache mit dem Therapeuten bar oder per Banküberweisung bezahlt."},
    {"step": "04", "text": "Nach Therapieende erhalten Sie eine Honorarnote für die anteilige Rückerstattung durch die Krankenkasse."},
]))

# ============================================================
# PHYSIOTHERAPIE / CTA
# ============================================================
out.append(section("PHYSIOTHERAPIE / CTA"))
out.append(fields_block("physiotherapie", "cta", [
    ("headline",       "Termin vereinbaren",                                                        "text"),
    ("subtext",        "Klicken Sie auf den Therapeuten Ihrer Wahl – wir freuen uns, Sie willkommen zu heißen.", "textarea"),
    ("btn_primary",    "Zum Team & Terminen",                                                       "text"),
    ("btn_primary_url","#team",                                                                     "url"),
    ("btn_secondary",  "Kontakt aufnehmen",                                                         "text"),
    ("btn_secondary_url","/kontakt.php",                                                            "url"),
]))

# ============================================================
# FIRMENFITNESS / HERO
# ============================================================
out.append(section("FIRMENFITNESS / HERO"))
out.append(fields_block("firmenfitness", "hero", [
    ("label",           "Firmenfitness",                                                                       "text"),
    ("headline",        "Gesunde, motivierte\nMitarbeiter. Starke Teams.",                                     "text"),
    ("subtext",         "Trainings und Workshops direkt in Ihrem Unternehmen – für fitte Mitarbeiter und bessere Performance.", "textarea"),
    ("cta_primary",     "Jetzt anfragen",                                                                      "text"),
    ("cta_primary_url", "/kontakt.php",                                                                        "url"),
]))

# ============================================================
# FIRMENFITNESS / INTRO
# ============================================================
out.append(section("FIRMENFITNESS / INTRO"))
out.append(fields_block("firmenfitness", "intro", [
    ("section_label", "Betriebliche Gesundheitsvorsorge",                                                                     "text"),
    ("headline",      "Fitness als Investition in Ihr Unternehmen",                                                            "text"),
    ("text1",         "Sie suchen nach einer effektiven Möglichkeit, die Gesundheit und das Wohlbefinden Ihrer Mitarbeiter zu fördern und gleichzeitig die Produktivität am Arbeitsplatz zu steigern?\n\nHerzlich willkommen bei unserer exklusiven Firmenfitness in Wien. Wir bieten maßgeschneiderte Programme, die auf die Bedürfnisse Ihrer Firma zugeschnitten sind und Ihren Mitarbeitern helfen, sich fit zu halten, Stress abzubauen und ihre Leistungsfähigkeit zu optimieren.", "textarea"),
    ("why_headline",  "Warum Firmenfitness?",                                                                                  "text"),
    ("text2",         "Investitionen in die Gesundheit und das Wohlbefinden Ihrer Mitarbeiter haben nachweislich positive Auswirkungen auf das Arbeitsklima und die Unternehmensperformance.\n\nFördern Sie die Gesundheit und das Wohlbefinden Ihrer Mitarbeiter mit unserer maßgeschneiderten Firmenfitness in Wien. Mit unseren speziell auf Ihr Unternehmen zugeschnittenen Programmen können Sie die Produktivität steigern, das Arbeitsklima verbessern und sich als attraktiver Arbeitgeber positionieren. Investieren Sie in die Gesundheit Ihrer Mitarbeiter – Ihr Unternehmen wird es Ihnen danken!", "textarea"),
    ("cta_label",     "Unverbindlich anfragen →",                                                                              "text"),
    ("cta_url",       "/kontakt.php",                                                                                          "url"),
]))

# ============================================================
# FIRMENFITNESS / CTA
# ============================================================
out.append(section("FIRMENFITNESS / CTA"))
out.append(fields_block("firmenfitness", "cta", [
    ("headline",       "Investieren Sie in die Gesundheit Ihrer Mitarbeiter", "text"),
    ("subtext",        "Kontaktieren Sie uns für ein individuelles Angebot für Ihr Unternehmen.", "textarea"),
    ("btn_primary",    "Jetzt anfragen →",                                   "text"),
    ("btn_primary_url","/kontakt.php",                                       "url"),
]))

# ============================================================
# STUDIO / HERO
# ============================================================
out.append(section("STUDIO / HERO"))
out.append(fields_block("studio", "hero", [
    ("label",               "FlexFit Studio Wien 1080",                          "text"),
    ("headline",            "Privates Training –\nkein Gedränge, kein Stress",    "text"),
    ("subtext",             "Musterstraße 12, 1010 Wien · 2 Trainingsräume + 1 Therapieraum · Garderobe & Dusche", "textarea"),
    ("bg_image",            "/assets/img/studio/sumo-squat-hero.jpg",             "url"),
    ("cta_primary",         "Probetraining buchen",                               "text"),
    ("cta_primary_url",     "/probetraining.php",                                 "url"),
    ("cta_secondary",       "Raumvermietung",                                     "text"),
    ("cta_secondary_url",   "#raumvermietung",                                    "url"),
]))

# ============================================================
# STUDIO / INFO  (facility description)
# ============================================================
out.append(section("STUDIO / INFO"))
out.append(fields_block("studio", "info", [
    ("section_label", "Unser privates Studio",                                             "text"),
    ("headline",      "Voll ausgestattet.\nNur für dich.",                                 "text"),
    ("text",          "Bei FlexFit gibt es kein Warten auf Geräte. Unser privates, voll ausgestattetes Personal Training Studio bietet Ihnen absolute Privatsphäre. Die Trainingseinheiten finden völlig ungestört statt, ein Trainer arbeitet exklusiv mit Ihnen. Keine überfüllten Räume, kein Stress – reiner Fokus auf Ihr Training.", "textarea"),
    ("image",         "/assets/img/studio/sumo-squat-hero.jpg",                            "url"),
    ("cta_label",     "Studio kennenlernen →",                                             "text"),
    ("cta_url",       "/probetraining.php",                                                "url"),
]))
out.append(items_delete("studio", "info"))
out.append(items_insert("studio", "info", [
    {"text": "2 Trainingsräume + 1 Therapieraum"},
    {"text": "Kabelzüge, Racks, Multipress, Airbike, Lang- & Kurzhanteln, Kettlebells"},
    {"text": "Handtücher & Getränke inklusive"},
    {"text": "Garderobe & Dusche"},
    {"text": "Klimaanlage, WLAN, top Erreichbarkeit"},
]))

# ============================================================
# STUDIO / LOCATION  (transport options)
# ============================================================
out.append(section("STUDIO / LOCATION"))
out.append(fields_block("studio", "location", [
    ("section_label", "Erreichbarkeit",                                       "text"),
    ("headline",      "Zentral in Wien 1080",                                 "text"),
    ("subtext",       "Musterstraße 12 – bequem mit Öffis oder zu Fuß erreichbar", "textarea"),
    ("maps_embed",    "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2658.3!2d16.3508!3d48.2105!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x476d07c2e74cdca3%3A0x8f4ae0e76e4c4!2sMusterstraße+50%2C+1080+Wien!5e0!3m2!1sde!2sat!4v1", "url"),
]))
out.append(items_delete("studio", "location"))
out.append(items_insert("studio", "location", [
    {"icon": "🚇", "title": "Öffentlich",    "desc": "U6, Straßenbahn 9, 42 & 43"},
    {"icon": "🚶", "title": "Zu Fuß / Rad",  "desc": "Aus 1070, 1080, 1090, 1160, 1170, 1180, 1190"},
    {"icon": "🚗", "title": "Auto",          "desc": "Parkplätze in der Nähe vorhanden"},
]))

# ============================================================
# STUDIO / RAUMVERMIETUNG
# ============================================================
out.append(section("STUDIO / RAUMVERMIETUNG"))
out.append(fields_block("studio", "raumvermietung", [
    ("section_label", "Raumvermietung",                                                                              "text"),
    ("headline",      "Fitnessraum & Therapieraum mieten",                                                           "text"),
    ("subtext",       "Für selbständige Trainer, Physios, Masseure, Ernährungsberater und andere Gesundheitsexperten.", "textarea"),
    ("cta_label",     "Besichtigungstermin anfragen →",                                                              "text"),
    ("cta_url",       "/kontakt.php",                                                                                "url"),
]))
out.append(items_delete("studio", "raumvermietung"))
out.append(items_insert("studio", "raumvermietung", [
    {"icon": "🏋", "title": "Fitnessraum / Gym mieten",  "desc": "", "price": "", "price_note": ""},
    {"icon": "🪑", "title": "Therapieraum mieten",       "desc": "", "price": "", "price_note": ""},
]))

# ============================================================
# STUDIO / CTA
# ============================================================
out.append(section("STUDIO / CTA"))
out.append(fields_block("studio", "cta", [
    ("headline",         "Studio besichtigen",                                                       "text"),
    ("subtext",          "Vereinbaren Sie einen unverbindlichen Besichtigungstermin – wir freuen uns auf Sie.", "textarea"),
    ("btn_primary",      "Probetraining buchen →",                                                  "text"),
    ("btn_primary_url",  "/probetraining.php",                                                      "url"),
    ("btn_secondary",    "Kontakt aufnehmen",                                                       "text"),
    ("btn_secondary_url","/kontakt.php",                                                            "url"),
]))

# ============================================================
# PROBETRAINING / HERO
# ============================================================
out.append(section("PROBETRAINING / HERO"))
out.append(fields_block("probetraining", "hero", [
    ("label",   "Kostenlos & Unverbindlich",                                                                                           "text"),
    ("headline","Gratis Probetraining\nbei FlexFit Wien",                                                                             "text"),
    ("subtext", "Sichern Sie sich jetzt Ihr kostenloses, unverbindliches Probetraining (Gespräch & Training ca. 60 Min.) bei FlexFit Personal Training Wien.", "textarea"),
]))

# Write
output = "\n".join(out) + "\n"
out_path = r"sync_flexfit.sql"
with open(out_path, "w", encoding="utf-8") as f:
    f.write(output)
print(f"Written {len(output)} bytes to {out_path}")
