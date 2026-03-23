CREATE TABLE IF NOT EXISTS `users_states` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bot_id`     INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `state`      VARCHAR(255) NOT NULL,
    `data`       JSON         NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),

    UNIQUE KEY `unique_bot_user` (`bot_id`, `user_id`),

    INDEX `idx_bot_id` (`bot_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_state` (`state`),

    CONSTRAINT `fk_users_states_bot`
        FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_users_states_telegram_user`
        FOREIGN KEY (`user_id`) REFERENCES `telegram_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;