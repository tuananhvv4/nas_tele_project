CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `username`            VARCHAR(80)      NOT NULL UNIQUE,
    `password_hash`       VARCHAR(255)     NOT NULL,
    `display_name`        VARCHAR(120)     NOT NULL DEFAULT '',
    `email`               VARCHAR(180)     NULL,
    `role`                ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    `feature_permissions` JSON             NULL COMMENT 'Null = default, JSON object for admins',
    `max_bots`            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `status`              ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_by`          INT UNSIGNED     NULL COMMENT 'FK to admin_users (who created this admin)',
    `last_login_at`       DATETIME         NULL,
    `created_at`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME         NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
