-- ============================================================
-- Migration 005: Blog Posts
-- Per-project blog post management
-- ============================================================

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `project_id`      INT UNSIGNED    NOT NULL,
  `slug`            VARCHAR(160)    NOT NULL DEFAULT '',
  `title`           VARCHAR(255)    NOT NULL DEFAULT '',
  `excerpt`         TEXT            NOT NULL,
  `featured_image`  VARCHAR(500)    NOT NULL DEFAULT '',
  `author_name`     VARCHAR(120)    NOT NULL DEFAULT '',
  `author_photo`    VARCHAR(500)    NOT NULL DEFAULT '',
  `category`        VARCHAR(80)     NOT NULL DEFAULT '',
  `read_time`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `status`          ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `content`         LONGTEXT        NOT NULL COMMENT 'JSON array of content blocks',
  `seo_title`       VARCHAR(70)     NOT NULL DEFAULT '',
  `seo_description` VARCHAR(160)    NOT NULL DEFAULT '',
  `published_at`    DATETIME                 DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_slug` (`project_id`, `slug`),
  KEY `idx_project_status` (`project_id`, `status`, `published_at`),
  CONSTRAINT `fk_blog_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
