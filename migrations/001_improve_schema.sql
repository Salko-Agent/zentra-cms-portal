-- ============================================================
-- Zentra CMS – Migration 001: Schema Improvements
-- Run in phpMyAdmin on Hostinger
-- ============================================================

-- 1. Add updated_at to pages
ALTER TABLE `pages`
  ADD COLUMN `sort_order` SMALLINT NOT NULL DEFAULT 0 AFTER `noindex`,
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 2. Add updated_at to sections
ALTER TABLE `sections`
  ADD COLUMN `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 3. Add last_login + avatar to users
ALTER TABLE `users`
  ADD COLUMN `last_login` DATETIME DEFAULT NULL AFTER `created_at`;

-- 4. Add form_type to form_submissions (if missing)
ALTER TABLE `form_submissions`
  ADD COLUMN IF NOT EXISTS `form_type` VARCHAR(40) NOT NULL DEFAULT 'kontakt' AFTER `page_slug`,
  ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ip`;

-- 5. Activity log table
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(40)  NOT NULL,
  `target_type` VARCHAR(40)  NOT NULL DEFAULT '',
  `target_id`   INT UNSIGNED DEFAULT NULL,
  `target_label` VARCHAR(200) NOT NULL DEFAULT '',
  `meta_json`   TEXT         DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_project` (`project_id`, `created_at`),
  KEY `idx_log_user` (`user_id`),
  CONSTRAINT `fk_log_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Set initial updated_at for existing pages
UPDATE `pages` SET `updated_at` = `created_at` WHERE `updated_at` IS NULL;
