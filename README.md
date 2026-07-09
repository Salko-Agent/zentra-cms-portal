# 🖥️ Zentra CMS: Tailored Content Management System for Hand-Coded Sites

> Bespoke, lightweight content management and SEO dashboard designed for hand-coded websites. A fast, secure alternative to WordPress.

![Zentra CMS Showcase Mockup](assets/img/zentra-logo.svg) *(Logo placeholder)*

---

## 🔗 Live & Links
*   **Live Demo:** [bmsdigitalsolutions.com/demos/zentra-portal/](https://bmsdigitalsolutions.com/demos/zentra-portal/) *(Demo location on portfolio hub)*
*   **Tech Stack:** PHP 8.x, MySQL, Google PageSpeed API, Google Search Console API, Vanilla CSS/JS (Dark-Glow Theme)

---

## 💡 Project Overview

### ❌ The Challenge
Standard CMS platforms like WordPress introduce substantial code bloat, vulnerability risks from plugins, and poor page load performance for custom, hand-coded websites. However, clients still demand a simple way to edit page texts, swap images, and manage SEO metadata on their own without contacting a developer.

### 🛠️ The Solution
A **bespoke, database-driven PHP CMS** specifically engineered for flat or custom-built frontends. It enables rapid editing of page content via a dark, modern admin dashboard. Developers define content sections, and the CMS dynamically builds form fields for the client. The system also monitors performance metrics via the Google PageSpeed API and search traffic via the Google Search Console API.

### 🌟 Key Highlights
*   **⚡ Zero WordPress Bloat:** A minimal, secure PHP backend that serves admin panels in milliseconds while keeping the customer's frontend completely optimized.
*   **📈 Built-in SEO & Speed Monitoring:** Directly calls Google APIs inside the dashboard to track real-time PageSpeed scores and Search Console impressions, immediately catching performance regressions.
*   **🔄 Custom Section Forms:** The CMS matches the admin interface to predefined templates (such as Heroes, Pricing, Accordions, Team sections), eliminating client editing mistakes.
*   **🔒 Hardened Security:** Implements CSRF tokens, secure session management, SQL-injection prevention via prepared PDO statements, and strict role-based access.

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
