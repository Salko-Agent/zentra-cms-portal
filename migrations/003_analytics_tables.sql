-- ============================================================
-- Zentra CMS – Analytics Tables Migration
-- Run via phpMyAdmin on your_database_name
-- ============================================================

-- 1. GA4 Analytics Cache
CREATE TABLE IF NOT EXISTS `analytics_cache` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `metric_type`   VARCHAR(40)  NOT NULL,
  `date_range`    VARCHAR(20)  NOT NULL,
  `data_json`     MEDIUMTEXT   NOT NULL,
  `fetched_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_analytics` (`project_id`, `metric_type`, `date_range`),
  CONSTRAINT `fk_analytics_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Search Console Cache
CREATE TABLE IF NOT EXISTS `search_console_cache` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `metric_type`   VARCHAR(40)  NOT NULL,
  `date_range`    VARCHAR(20)  NOT NULL,
  `data_json`     MEDIUMTEXT   NOT NULL,
  `fetched_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gsc` (`project_id`, `metric_type`, `date_range`),
  CONSTRAINT `fk_gsc_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PageSpeed Insights Cache
CREATE TABLE IF NOT EXISTS `pagespeed_cache` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `url`           VARCHAR(500) NOT NULL,
  `strategy`      ENUM('mobile','desktop') NOT NULL DEFAULT 'mobile',
  `scores_json`   MEDIUMTEXT   NOT NULL,
  `vitals_json`   MEDIUMTEXT   NOT NULL,
  `fetched_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pagespeed` (`project_id`, `url`(191), `strategy`),
  CONSTRAINT `fk_pagespeed_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SEO Audit Cache
CREATE TABLE IF NOT EXISTS `seo_audit_cache` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `url`           VARCHAR(500) NOT NULL,
  `score`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `issues_json`   MEDIUMTEXT   NOT NULL,
  `audited_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_audit` (`project_id`, `url`(191)),
  CONSTRAINT `fk_seo_audit_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Google OAuth Token Cache (shared across all projects)
CREATE TABLE IF NOT EXISTS `google_tokens` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `scope`         VARCHAR(255) NOT NULL,
  `access_token`  TEXT         NOT NULL,
  `expires_at`    DATETIME     NOT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_scope` (`scope`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
