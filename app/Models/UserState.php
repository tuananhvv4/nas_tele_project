<?php

declare(strict_types=1);

namespace App\Models;

class UserState extends BaseModel
{
    protected static string $table = 'users_states';

    public static function getUserState($botId, $userId): ?array
    {
        $botId = (int) $botId;
        $userId = (int) $userId;
        $row = static::db()->table(static::$table)
            ->where('bot_id', $botId)->where('user_id', $userId)->first();
        if (!$row) return [];
        return $row;
    }

    public static function setUserState($botId, $userId, string $state, array $data = []): void
    {
        $botId = (int) $botId;
        $userId = (int) $userId;
        $existing = static::db()->table(static::$table)
            ->where('bot_id', $botId)->where('user_id', $userId)->first();

        if ($existing) {
            static::db()->table(static::$table)
                ->where('id', $existing['id'])
                ->update([
                    'state' => $state, 
                    'data' => $data ? json_encode($data) : null, 
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            static::db()->table(static::$table)->insert([
                'bot_id' => $botId,
                'user_id' => $userId,
                'state' => $state,
                'data' => $data ? json_encode($data) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
