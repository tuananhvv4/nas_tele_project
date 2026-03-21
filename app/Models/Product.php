<?php

declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel
{
    protected static string $table = 'products';

    public static function forBot(int $botId, array $filters = []): array
    {
        $q = static::db()->table(static::$table)->where('bot_id', $botId);
        if (!empty($filters['category_id'])) $q = $q->where('category_id', $filters['category_id']);
        if (!empty($filters['status']))      $q = $q->where('status', $filters['status']);
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
        return $slug ?: 'product';
    }

    public static function paginateForBot(int $botId, int $perPage, int $page, array $filters = []): array
    {
        $q = static::db()->table(static::$table)->where('bot_id', $botId);
        if (!empty($filters['category_id'])) $q = $q->where('category_id', $filters['category_id']);
        if (!empty($filters['status']))      $q = $q->where('status', $filters['status']);
        return $q->orderBy('id', 'DESC')->paginate($perPage, $page);
    }
}
