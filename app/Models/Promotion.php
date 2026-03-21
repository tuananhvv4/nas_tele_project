<?php

declare(strict_types=1);

namespace App\Models;

class Promotion extends BaseModel
{
    protected static string $table = 'promotions';

    public static function paginateForBot($botId, $perPage, $page): array
    {
        $botId  = (int) $botId;
        $perPage = (int) $perPage;
        $page   = (int) $page;
        return static::db()->table('promotions')
            ->where('bot_id', $botId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function findForBot($id, $botId): ?array
    {
        $id    = (int) $id;
        $botId = (int) $botId;
        return static::db()->table('promotions')
            ->where('id', $id)->where('bot_id', $botId)->first();
    }

    public static function findByCode($botId, string $code): ?array
    {
        $botId = (int) $botId;
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

    public static function incrementUsed($id): void
    {
        $id = (int) $id;
        static::db()->query(
            "UPDATE promotions SET used_count = used_count + 1 WHERE id = :id",
            ['id' => $id]
        );
    }
}
