CREATE TABLE IF NOT EXISTS `api_sources` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`         INT UNSIGNED NOT NULL,
    `name`           VARCHAR(120) NOT NULL,
    `base_url`       VARCHAR(500) NOT NULL,
    `api_key`        VARCHAR(255) NULL,
    `type`           VARCHAR(60)  NOT NULL DEFAULT 'rest' COMMENT 'rest, graphql, etc.',
    `headers`        JSON         NULL COMMENT 'Extra request headers',
    `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
    `last_synced_at` DATETIME     NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_bot_id` (`bot_id`),
    CONSTRAINT `fk_api_sources_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
