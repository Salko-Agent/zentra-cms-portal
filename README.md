# 🖥️ Zentra CMS: Tailored Content Management System for Hand-Coded Sites

> BESPOKE, lightweight content management and SEO dashboard designed for hand-coded websites. A fast, secure alternative to WordPress.

![Zentra CMS Showcase Mockup](assets/img/zentra-logo.svg) *(Logo placeholder)*

---

## 🔗 Live & Links
*   **Live Demo:** [bmsdigitalsolutions.com/demos/zentra-portal/](https://bmsdigitalsolutions.com/demos/zentra-portal/) *(Demo location on portfolio hub)*
*   **Tech Stack:** PHP 8.x, MySQL, Google PageSpeed API, Google Search Console API, Vanilla CSS/JS (Dark-Glow Theme)

---

## 💡 Project Overview

### ❌ Was war das Problem?
Klassische CMS-Lösungen wie WordPress sind für handcodierte, performante Websites oft zu überladen und stellen durch veraltete Plugins ein permanentes Sicherheitsrisiko dar. Kunden möchten jedoch trotzdem eine einfache Möglichkeit, Texte zu bearbeiten, Bilder auszutauschen oder SEO-Metadaten anzupassen, ohne jedes Mal den Code anfassen zu müssen.

### 🛠️ Was habe ich gebaut?
Ein **maßgeschneidertes, datenbankgestütztes PHP-CMS** für statische und handcodierte Websites. Es ermöglicht eine einfache Steuerung der Website-Inhalte über ein performantes, dunkel gestaltetes Admin-Dashboard. Entwickler definieren Abschnitte (Sections), und Kunden können die Datenfelder direkt im Browser ändern. Zudem überwacht das CMS die Ladezeiten (PageSpeed API) und Klickdaten (Google Search Console API) der Website.

### 🌟 Was ist besonders?
*   **⚡ Zero-WordPress-Overhead:** Extrem schlanker PHP-Code ohne Plugins. Seiten laden im CMS-Bereich in wenigen Millisekunden und das Frontend bleibt absolut geschwindigkeitsoptimiert.
*   **📈 Integriertes SEO- & Speed-Audit:** Direkt im Dashboard werden Live-Testergebnisse von Google PageSpeed und Leistungskennzahlen der Search Console ausgewertet, um Performance-Einbrüche sofort zu erkennen.
*   **🔄 Custom Section Builder:** Das System generiert dynamisch Formulare für vordefinierte Website-Elemente (z. B. Heroes, Preistabellen, FAQ-Akkordeons, Teamblocks), was Fehleingaben der Kunden verhindert.
*   **🔒 Hardened Security:** Vollständiger Schutz vor CSRF, sichere Passwort-Verschlüsselung, SQL-Injection-Schutz über präparierte Statements (PDO) und rollenbasierte Zugriffsrechte.

---

## 🚀 Setup & Local Deployment

### Prerequisites
*   Web server with PHP 8.0+ support (e.g., Apache, Laragon, XAMPP)
*   MySQL/MariaDB Database

### Installation
1.  **Clone the repository:**
    ```bash
    git clone https://github.com/Salko-Agent/zentra-cms-portal.git
    cd zentra-cms-portal
    ```
2.  **Configure environment:**
    *   Copy `config.php` to adapt your database credentials.
    *   Create a Google Service Account key and place it at `data/google-service-account.json` (use `google-service-account.json.example` as a template) to enable API analytics.
3.  **Initialize Database:**
    *   Import `schema.sql` into your database to set up table structures.
4.  **Host and Run:**
    *   Point your server host to the directory and access via your browser (e.g., `http://localhost:8081`).
