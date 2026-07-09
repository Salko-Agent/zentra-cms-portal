# 🖥️ Zentra CMS: Tailored Content Management System for Hand-Coded Sites

Zentra CMS is a lightweight, custom-built Content Management System (CMS) designed specifically to manage hand-coded, static-first websites. Created as a robust and speed-optimized alternative to bloated systems like WordPress, Zentra empowers developers and clients to edit content, manage SEO metadata, and update imagery through an elegant, low-latency dashboard.

---

## ⚡ Key Capabilities & Features

*   **🛠️ Zero-WordPress Bloat:** Minimal PHP footprint with optimized page speed performance. Runs on standard Apache environments with SQLite/MySQL backends.
*   **📊 SEO & Page Speed Monitoring:** Integrated dashboard displaying real-time SEO audit stats and PageSpeed metrics via Google API connection.
*   **🔄 Database Synchronization Engine:** Automation script (`gen_sync_sql.py`) to generate sync scripts and coordinate local development databases with live production setups.
*   **🔒 Built-In Security Framework:** CSRF protection, secure session lifecycles, and role-based client routing out-of-the-box.
*   **📂 Structured Asset Manager:** Direct file upload and template asset preview pipelines.

---

## 🛠️ Technical Details

*   **Backend:** PHP 8+, MySQL / MariaDB, Google PageSpeed API integration.
*   **Frontend Interface:** Modern, responsive dark-themed dashboard built with vanilla CSS (featuring dynamic CSS metaball/lava-lamp backgrounds).
*   **Database Schema:** Clean relation-based tables for users, roles, pages, blocks, and activity audits (defined in `schema.sql`).

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
