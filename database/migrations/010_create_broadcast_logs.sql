CREATE TABLE IF NOT EXISTS `broadcast_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`      INT UNSIGNED NOT NULL,
    `message`     TEXT         NOT NULL,
    `media_url`   VARCHAR(500) NULL,
    `target`      ENUM('all','active') NOT NULL DEFAULT 'all',
    `sent_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `fail_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `status`      ENUM('pending','sending','done','failed') NOT NULL DEFAULT 'pending',
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_bot_id` (`bot_id`),
    CONSTRAINT `fk_broadcast_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
