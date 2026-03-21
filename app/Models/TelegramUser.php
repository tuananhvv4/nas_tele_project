<?php

declare(strict_types=1);

namespace App\Models;

class TelegramUser extends BaseModel
{
    protected static string $table = 'telegram_users';

    public static function findOrCreate(int $botId, array $data): array
    {
        $db = static::db();
        $existing = $db->table(static::$table)
            ->where('bot_id', $botId)
            ->where('telegram_id', $data['telegram_id'])
            ->first();

        if ($existing) {
            // Update name if changed
            $db->table(static::$table)
                ->where('id', $existing['id'])
                ->update([
                    'username'   => $data['username'] ?? null,
                    'first_name' => $data['first_name'] ?? '',
                    'last_name'  => $data['last_name'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            return array_merge($existing, $data);
        }

        $id = $db->table(static::$table)->insert([
            'bot_id'      => $botId,
            'telegram_id' => $data['telegram_id'],
            'username'    => $data['username'] ?? null,
            'first_name'  => $data['first_name'] ?? '',
            'last_name'   => $data['last_name'] ?? null,
            'language'    => $data['language'] ?? null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['id' => $id], $data);
    }

    public static function paginateForBot(int $botId, int $perPage, int $page): array
    {
        return static::db()->table(static::$table)
            ->where('bot_id', $botId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function findForBot(int $id, int $botId): ?array
    {
        return static::db()->table(static::$table)
            ->where('id', $id)->where('bot_id', $botId)->first();
    }

    public static function toggleBan(int $id): void
    {
        $user = static::find($id);
        if ($user) {
            static::update($id, ['is_banned' => $user['is_banned'] ? 0 : 1]);
        }
    }
}
