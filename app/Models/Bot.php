<?php

declare(strict_types=1);

namespace App\Models;

class Bot extends BaseModel
{
    protected static string $table = 'bots';

    public static function forAdmin(int $adminUserId): array
    {
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public static function findForAdmin(int $botId, int $adminUserId): ?array
    {
        return static::db()
            ->table(static::$table)
            ->where('id', $botId)
            ->where('admin_user_id', $adminUserId)
            ->first();
    }

    public static function countForAdmin(int $adminUserId): int
    {
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->count();
    }

    public static function paginateForAdmin(int $adminUserId, int $perPage, int $page): array
    {
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }
}
