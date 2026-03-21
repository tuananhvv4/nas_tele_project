<?php

declare(strict_types=1);

namespace App\Models;

class BroadcastLog extends BaseModel
{
    protected static string $table = 'broadcast_logs';

    public static function paginateForBot(int $botId, int $perPage, int $page): array
    {
        return static::db()->table('broadcast_logs')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function findForBot(int $id, int $botId): ?array
    {
        return static::db()->table('broadcast_logs')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }
}
