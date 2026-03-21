<?php

declare(strict_types=1);

namespace App\Models;

class ApiSource extends BaseModel
{
    protected static string $table = 'api_sources';

    public static function paginateForBot($botId, $perPage, $page): array
    {
        $botId  = (int) $botId;
        $perPage = (int) $perPage;
        $page   = (int) $page;
        return static::db()->table('api_sources')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function forBot($botId): array
    {
        $botId = (int) $botId;
        return static::db()->table('api_sources')
            ->where('bot_id', $botId)->where('is_active', 1)->get();
    }

    public static function findForBot($id, $botId): ?array
    {
        $id    = (int) $id;
        $botId = (int) $botId;
        return static::db()->table('api_sources')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }
}
