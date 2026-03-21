<?php

declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel
{
    protected static string $table = 'categories';

    public static function forBot(int $botId, bool $onlyActive = false): array
    {
        $q = static::db()->table(static::$table)->where('bot_id', $botId);
        if ($onlyActive) $q = $q->where('status', 'active');
        return $q->orderBy('sort_order')->orderBy('name')->get();
    }

    public static function findForBot(int $id, int $botId): ?array
    {
        return static::db()->table(static::$table)
            ->where('id', $id)->where('bot_id', $botId)->first();
    }

    public static function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', trim($slug));
        return $slug ?: 'category';
    }
}
