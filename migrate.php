<?php
/**
 * Database Migration Runner
 * Usage: php migrate.php
 * Reads DB config from .env in the same directory.
 */

// ── Load .env ─────────────────────────────────────────────────────────────────
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "[ERROR] .env file not found at {$envFile}\n";
    exit(1);
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

$host     = $env['DB_HOST']     ?? '127.0.0.1';
$port     = $env['DB_PORT']     ?? '3306';
$dbName   = $env['DB_DATABASE'] ?? 'telebot_v1';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

// ── Connect (without selecting a DB first so we can CREATE it) ────────────────
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "[ERROR] Cannot connect to MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// ── Create database if not exists ────────────────────────────────────────────
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbName}`");
echo "Database `{$dbName}` ready.\n\n";

// ── Migration definitions ─────────────────────────────────────────────────────
$migrations = [

    '001 admin_users' => "
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
    ",

    '002 bots' => "
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
    ",

    '003 categories' => "
        CREATE TABLE IF NOT EXISTS `categories` (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `bot_id`     INT UNSIGNED NOT NULL,
            `parent_id`  INT UNSIGNED NULL,
            `name`       VARCHAR(150) NOT NULL,
            `slug`       VARCHAR(180) NOT NULL,
            `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_bot_slug` (`bot_id`, `slug`),
            INDEX `idx_bot_id` (`bot_id`),
            INDEX `idx_parent_id` (`parent_id`),
            CONSTRAINT `fk_categories_bot`    FOREIGN KEY (`bot_id`)    REFERENCES `bots` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    '004 products' => "
        CREATE TABLE IF NOT EXISTS `products` (
            `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            `bot_id`      INT UNSIGNED   NOT NULL,
            `category_id` INT UNSIGNED   NULL,
            `name`        VARCHAR(255)   NOT NULL,
            `slug`        VARCHAR(280)   NOT NULL,
            `description` TEXT           NULL,
            `price`       DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `sale_price`  DECIMAL(15,2)  NULL,
            `stock`       INT            NOT NULL DEFAULT -1 COMMENT '-1 = unlimited',
            `status`      ENUM('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
            `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            `created_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_bot_slug` (`bot_id`, `slug`),
            INDEX `idx_bot_id` (`bot_id`),
            INDEX `idx_category_id` (`category_id`),
            INDEX `idx_status` (`status`),
            CONSTRAINT `fk_products_bot`      FOREIGN KEY (`bot_id`)      REFERENCES `bots` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    '005 telegram_users' => "
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
    ",

    '006 promotions' => "
        CREATE TABLE IF NOT EXISTS `promotions` (
            `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            `bot_id`       INT UNSIGNED   NOT NULL,
            `code`         VARCHAR(80)    NOT NULL,
            `description`  VARCHAR(255)   NULL,
            `type`         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
            `value`        DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `min_order`    DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `max_uses`     INT            NULL COMMENT 'NULL = unlimited',
            `used_count`   INT            NOT NULL DEFAULT 0,
            `start_at`     DATETIME       NULL,
            `end_at`       DATETIME       NULL,
            `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`   DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_bot_code` (`bot_id`, `code`),
            INDEX `idx_bot_id` (`bot_id`),
            CONSTRAINT `fk_promotions_bot` FOREIGN KEY (`bot_id`) REFERENCES `bots` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    '007 orders' => "
        CREATE TABLE IF NOT EXISTS `orders` (
            `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            `bot_id`           INT UNSIGNED   NOT NULL,
            `telegram_user_id` INT UNSIGNED   NOT NULL,
            `promo_id`         INT UNSIGNED   NULL,
            `promo_code`       VARCHAR(80)    NULL,
            `subtotal`         DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `discount_amount`  DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `total_amount`     DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
            `status`           ENUM('pending','confirmed','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
            `note`             TEXT           NULL,
            `shipping_info`    JSON           NULL,
            `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_bot_id` (`bot_id`),
            INDEX `idx_telegram_user_id` (`telegram_user_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `fk_orders_bot`           FOREIGN KEY (`bot_id`)           REFERENCES `bots` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_orders_telegram_user` FOREIGN KEY (`telegram_user_id`) REFERENCES `telegram_users` (`id`),
            CONSTRAINT `fk_orders_promo`         FOREIGN KEY (`promo_id`)         REFERENCES `promotions` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    '008 order_items' => "
        CREATE TABLE IF NOT EXISTS `order_items` (
            `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            `order_id`     INT UNSIGNED   NOT NULL,
            `product_id`   INT UNSIGNED   NULL,
            `product_name` VARCHAR(255)   NOT NULL COMMENT 'Snapshot at order time',
            `unit_price`   DECIMAL(15,2)  NOT NULL,
            `quantity`     INT UNSIGNED   NOT NULL DEFAULT 1,
            `subtotal`     DECIMAL(15,2)  NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_order_id` (`order_id`),
            CONSTRAINT `fk_order_items_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    '009 settings' => "
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
    ",

    '010 broadcast_logs' => "
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
    ",

    '011 api_sources' => "
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
    ",
];

// ── Run migrations ────────────────────────────────────────────────────────────
$passed = 0;
$failed = 0;

foreach ($migrations as $label => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK] {$label}\n";
        $passed++;
    } catch (PDOException $e) {
        echo "[FAIL] {$label}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n";
echo str_repeat('-', 40) . "\n";
echo "Done: {$passed} passed, {$failed} failed.\n";

if ($failed === 0) {
    echo "\nAll tables ready. Run the seeder next:\n";
    echo "  php database/seeds/SuperAdminSeeder.php\n";
}
