<?php

declare(strict_types=1);

namespace App\Models;

class Bot extends BaseModel
{
    protected static string $table = 'bots';

    public static function forAdmin($adminUserId): array
    {
        $adminUserId = (int) $adminUserId;
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public static function findForAdmin($botId, $adminUserId): ?array
    {
        $botId       = (int) $botId;
        $adminUserId = (int) $adminUserId;
        return static::db()
            ->table(static::$table)
            ->where('id', $botId)
            ->where('admin_user_id', $adminUserId)
            ->first();
    }

    public static function countForAdmin($adminUserId): int
    {
        $adminUserId = (int) $adminUserId;
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->count();
    }

    public static function paginateForAdmin($adminUserId, $perPage, $page): array
    {
        $adminUserId = (int) $adminUserId;
        $perPage     = (int) $perPage;
        $page        = (int) $page;
        return static::db()
            ->table(static::$table)
            ->where('admin_user_id', $adminUserId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }
}
