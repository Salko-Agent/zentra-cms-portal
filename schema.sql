-- ============================================================
-- Zentra Services – MySQL Schema v1.0
-- Deploy to: zentra.services MySQL database
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- projects
-- ============================================================
CREATE TABLE IF NOT EXISTS `projects` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120) NOT NULL,
  `project_key`  VARCHAR(60)  NOT NULL UNIQUE,
  `domain`       VARCHAR(120) NOT NULL DEFAULT '',
  `api_key`      VARCHAR(64)  NOT NULL,
  `webhook_url`  VARCHAR(255) NOT NULL DEFAULT '',
  `plan`         ENUM('free','starter','pro') NOT NULL DEFAULT 'pro',
  `active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_key` (`project_key`),
  KEY `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED NOT NULL,
  `name`         VARCHAR(120)  NOT NULL,
  `email`        VARCHAR(180)  NOT NULL,
  `pw_hash`      VARCHAR(255)  NOT NULL,
  `role`         ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login`   DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`project_id`, `email`),
  CONSTRAINT `fk_users_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- pages
-- ============================================================
CREATE TABLE IF NOT EXISTS `pages` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`      INT UNSIGNED NOT NULL,
  `slug`            VARCHAR(80)  NOT NULL,
  `label`           VARCHAR(120) NOT NULL,
  `seo_title`       VARCHAR(255) NOT NULL DEFAULT '',
  `seo_description` VARCHAR(500) NOT NULL DEFAULT '',
  `og_title`        VARCHAR(255) NOT NULL DEFAULT '',
  `og_description`  VARCHAR(500) NOT NULL DEFAULT '',
  `og_image`        VARCHAR(500) NOT NULL DEFAULT '',
  `noindex`         TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`      SMALLINT     NOT NULL DEFAULT 0,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_slug` (`project_id`, `slug`),
  CONSTRAINT `fk_pages_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- sections
-- ============================================================
CREATE TABLE IF NOT EXISTS `sections` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id`       INT UNSIGNED NOT NULL,
  `section_key`   VARCHAR(80)  NOT NULL,
  `label`         VARCHAR(120) NOT NULL DEFAULT '',
  `section_type`  VARCHAR(40)  NOT NULL,
  `enabled`       TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`    SMALLINT     NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_key` (`page_id`, `section_key`),
  CONSTRAINT `fk_sections_page` FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- section_fields  (scalar values: text, textarea, url, bool)
-- ============================================================
CREATE TABLE IF NOT EXISTS `section_fields` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id`  INT UNSIGNED NOT NULL,
  `field_key`   VARCHAR(80)  NOT NULL,
  `field_value` MEDIUMTEXT   NOT NULL,
  `field_type`  ENUM('text','textarea','url','bool','number','color','select','json') NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_field_key` (`section_id`, `field_key`),
  CONSTRAINT `fk_fields_section` FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- section_items  (repeater rows: each row is a JSON object)
-- ============================================================
CREATE TABLE IF NOT EXISTS `section_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id`  INT UNSIGNED NOT NULL,
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  `item_json`   MEDIUMTEXT   NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_items_section` FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- media
-- ============================================================
CREATE TABLE IF NOT EXISTS `media` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `filename`      VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL DEFAULT '',
  `url`           VARCHAR(500) NOT NULL,
  `file_size`     INT UNSIGNED NOT NULL DEFAULT 0,
  `mime_type`     VARCHAR(60)  NOT NULL DEFAULT '',
  `alt_text`      VARCHAR(255) NOT NULL DEFAULT '',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_project` (`project_id`),
  CONSTRAINT `fk_media_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- site_settings  (key→value store per project)
-- ============================================================
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `setting_key`   VARCHAR(80)  NOT NULL,
  `setting_value` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`project_id`, `setting_key`),
  CONSTRAINT `fk_settings_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- form_submissions  (contact / probetraining form submissions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED NOT NULL,
  `page_slug`   VARCHAR(80)  NOT NULL DEFAULT 'kontakt',
  `data_json`   MEDIUMTEXT NOT NULL,
  `ip`          VARCHAR(45)  NOT NULL DEFAULT '',
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sub_project` (`project_id`),
  CONSTRAINT `fk_sub_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- activity_log
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED NOT NULL,
  `user_id`      INT UNSIGNED DEFAULT NULL,
  `action`       VARCHAR(40)  NOT NULL,
  `target_type`  VARCHAR(40)  NOT NULL DEFAULT '',
  `target_id`    INT UNSIGNED DEFAULT NULL,
  `target_label` VARCHAR(200) NOT NULL DEFAULT '',
  `meta_json`    TEXT         DEFAULT NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_project` (`project_id`, `created_at`),
  KEY `idx_log_user` (`user_id`),
  CONSTRAINT `fk_log_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
