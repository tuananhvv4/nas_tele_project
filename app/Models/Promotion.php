<?php

declare(strict_types=1);

namespace App\Models;

class Promotion extends BaseModel
{
    protected static string $table = 'promotions';

    public static function paginateForBot(int $botId, int $perPage, int $page): array
    {
        return static::db()->table('promotions')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function findForBot(int $id, int $botId): ?array
    {
        return static::db()->table('promotions')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }

    public static function findByCode(int $botId, string $code): ?array
    {
        return static::db()->table('promotions')
            ->where('bot_id', $botId)->where('code', strtoupper($code))->first();
    }

    public static function isValid(array $promo): bool
    {
        if (!$promo['is_active']) return false;
        if ($promo['max_uses'] > 0 && $promo['used_count'] >= $promo['max_uses']) return false;
        $now = date('Y-m-d H:i:s');
        if ($promo['start_at'] && $promo['start_at'] > $now) return false;
        if ($promo['end_at'] && $promo['end_at'] < $now) return false;
        return true;
    }

    public static function incrementUsed(int $id): void
    {
        static::db()->query(
            "UPDATE promotions SET used_count = used_count + 1 WHERE id = :id",
            ['id' => $id]
        );
    }
}
