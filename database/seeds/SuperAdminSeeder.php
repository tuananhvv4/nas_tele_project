<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

$username = $_ENV['SUPER_ADMIN_USERNAME'] ?? 'superadmin';
$password = $_ENV['SUPER_ADMIN_PASSWORD'] ?? 'changeme123';

if (empty($username) || empty($password)) {
    echo "❌  Set SUPER_ADMIN_USERNAME and SUPER_ADMIN_PASSWORD in .env first.\n";
    exit(1);
}

$host   = $_ENV['DB_HOST']     ?? '127.0.0.1';
$port   = $_ENV['DB_PORT']     ?? '3306';
$dbname = $_ENV['DB_DATABASE'] ?? '';
$user   = $_ENV['DB_USERNAME'] ?? 'root';
$pass   = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "❌  Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if super admin already exists
$stmt = $pdo->prepare("SELECT id FROM admin_users WHERE role = 'super_admin' LIMIT 1");
$stmt->execute();
if ($stmt->fetch()) {
    echo "ℹ️   Super admin already exists. Skipping.\n";
    exit(0);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("
    INSERT INTO admin_users (username, password_hash, display_name, role, status, created_at)
    VALUES (:username, :hash, 'Super Admin', 'super_admin', 'active', NOW())
");
$stmt->execute([':username' => $username, ':hash' => $hash]);

echo "✅  Super admin created: {$username}\n";
