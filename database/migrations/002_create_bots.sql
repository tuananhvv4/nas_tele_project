CREATE TABLE IF NOT EXISTS `bots` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_user_id`   INT UNSIGNED NOT NULL,
    `name`            VARCHAR(120) NOT NULL,
    `bot_token`       VARCHAR(255) NOT NULL UNIQUE,
    `bot_username`    VARCHAR(120) NULL,
    `webhook_url`     VARCHAR(500) NULL,
    `webhook_status`  ENUM('not_set','active','inactive') NOT NULL DEFAULT 'not_set',
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_admin_user_id` (`admin_user_id`),
    CONSTRAINT `fk_bots_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
