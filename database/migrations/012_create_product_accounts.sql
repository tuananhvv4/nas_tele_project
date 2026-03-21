CREATE TABLE IF NOT EXISTS `product_accounts` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `value`      TEXT NOT NULL COMMENT 'Account data, e.g. username:password',
    `note`       VARCHAR(255) NULL,
    `status`     ENUM('available','used') NOT NULL DEFAULT 'available',
    `used_at`    DATETIME NULL,
    `order_id`   INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_product_accounts_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
