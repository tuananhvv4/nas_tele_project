CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`     INT UNSIGNED NOT NULL,
    `parent_id`  INT UNSIGNED NULL,
    `name`       VARCHAR(150) NOT NULL,
    `slug`        VARCHAR(180) NOT NULL,
    `description` TEXT           NULL,
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bot_slug` (`bot_id`, `slug`),
    INDEX `idx_bot_id` (`bot_id`),
    INDEX `idx_parent_id` (`parent_id`),
    CONSTRAINT `fk_categories_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
