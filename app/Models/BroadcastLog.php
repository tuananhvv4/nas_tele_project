<?php

declare(strict_types=1);

namespace App\Models;

class BroadcastLog extends BaseModel
{
    protected static string $table = 'broadcast_logs';

    public static function paginateForBot($botId, $perPage, $page): array
    {
        $botId  = (int) $botId;
        $perPage = (int) $perPage;
        $page   = (int) $page;
        return static::db()->table('broadcast_logs')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function findForBot($id, $botId): ?array
    {
        $id    = (int) $id;
        $botId = (int) $botId;
        return static::db()->table('broadcast_logs')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }
}
