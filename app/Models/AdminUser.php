<?php

declare(strict_types=1);

namespace App\Models;

class AdminUser extends BaseModel
{
    protected static string $table = 'admin_users';

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN       = 'admin';

    // ── Auth ──────────────────────────────────────────────────────────────────

    public static function findByUsername(string $username): ?array
    {
        return static::findBy('username', $username);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // ── Permissions ───────────────────────────────────────────────────────────

    public static function getPermissions(int $adminId): array
    {
        $user = static::find($adminId);
        if (!$user) return [];

        if ($user['role'] === self::ROLE_SUPER_ADMIN) {
            return static::fullPermissions();
        }

        $perms = $user['feature_permissions'] ?? '{}';
        return json_decode($perms, true) ?? [];
    }

    public static function can(int $adminId, string $feature): bool
    {
        $user = static::find($adminId);
        if (!$user) return false;
        if ($user['role'] === self::ROLE_SUPER_ADMIN) return true;

        $perms = json_decode($user['feature_permissions'] ?? '{}', true) ?? [];
        return (bool)($perms[$feature] ?? false);
    }

    public static function updatePermissions(int $adminId, array $permissions): int
    {
        return static::update($adminId, [
            'feature_permissions' => json_encode($permissions),
        ]);
    }

    public static function fullPermissions(): array
    {
        return [
            'products'    => true,
            'categories'  => true,
            'orders'      => true,
            'broadcast'   => true,
            'promotions'  => true,
            'api_sources' => true,
            'settings'    => true,
            'bots'        => true,
            'max_products'=> 9999,
            'max_bots'    => 99,
        ];
    }

    public static function defaultPermissions(): array
    {
        return [
            'products'    => true,
            'categories'  => true,
            'orders'      => true,
            'broadcast'   => false,
            'promotions'  => false,
            'api_sources' => false,
            'settings'    => true,
            'bots'        => true,
            'max_products'=> 100,
            'max_bots'    => 1,
        ];
    }

    // ── Scoped queries ────────────────────────────────────────────────────────

    public static function allAdmins(): array
    {
        return static::db()
            ->table(static::$table)
            ->where('role', self::ROLE_ADMIN)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
