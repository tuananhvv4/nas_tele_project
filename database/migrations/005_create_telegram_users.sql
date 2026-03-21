CREATE TABLE IF NOT EXISTS `telegram_users` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`      INT UNSIGNED NOT NULL,
    `telegram_id` BIGINT       NOT NULL,
    `username`    VARCHAR(120) NULL,
    `first_name`  VARCHAR(120) NOT NULL DEFAULT '',
    `last_name`   VARCHAR(120) NULL,
    `language`    VARCHAR(10)  NULL,
    `is_banned`   TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bot_telegram` (`bot_id`, `telegram_id`),
    INDEX `idx_bot_id` (`bot_id`),
    CONSTRAINT `fk_telegram_users_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
