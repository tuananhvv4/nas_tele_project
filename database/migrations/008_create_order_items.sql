CREATE TABLE IF NOT EXISTS `order_items` (
    `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `order_id`    INT UNSIGNED   NOT NULL,
    `product_id`  INT UNSIGNED   NULL,
    `product_name`VARCHAR(255)   NOT NULL COMMENT 'Snapshot at order time',
    `unit_price`  DECIMAL(15,2)  NOT NULL,
    `quantity`    INT UNSIGNED   NOT NULL DEFAULT 1,
    `subtotal`    DECIMAL(15,2)  NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_order_id` (`order_id`),
    CONSTRAINT `fk_order_items_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
