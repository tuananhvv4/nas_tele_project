CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`     INT UNSIGNED NOT NULL,
    `key`        VARCHAR(120) NOT NULL,
    `value`      TEXT         NULL,
    `type`       ENUM('text','json','bool','int') NOT NULL DEFAULT 'text',
    `label`      VARCHAR(150) NULL COMMENT 'Human-readable label for admin UI',
    `updated_at` DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bot_key` (`bot_id`, `key`),
    INDEX `idx_bot_id` (`bot_id`),
    CONSTRAINT `fk_settings_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
