<?php

declare(strict_types=1);

namespace App\Models;

class ApiSource extends BaseModel
{
    protected static string $table = 'api_sources';

    public static function paginateForBot(int $botId, int $perPage, int $page): array
    {
        return static::db()->table('api_sources')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function forBot(int $botId): array
    {
        return static::db()->table('api_sources')
            ->where('bot_id', $botId)->where('is_active', 1)->get();
    }

    public static function findForBot(int $id, int $botId): ?array
    {
        return static::db()->table('api_sources')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }
}
