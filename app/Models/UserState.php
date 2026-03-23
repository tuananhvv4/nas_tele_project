<?php

declare(strict_types=1);

namespace App\Models;

class UserState extends BaseModel
{
    protected static string $table = 'users_states';

    public static function getUserState($botId, $userId): ?string
    {
        $botId = (int) $botId;
        $userId = (int) $userId;
        $row = static::db()->table('users_states')
            ->where('bot_id', $botId)->where('user_id', $userId)->first();
        if (!$row) return null;
        return $row['state'];
    }

    public static function setUserState($botId, $userId, string $newState, array $data = []): void
    {
        $botId = (int) $botId;
        $userId = (int) $userId;
        $existing = static::db()->table('users_states')
            ->where('bot_id', $botId)->where('user_id', $userId)->first();

        if ($existing) {
            static::db()->table('users_states')
                ->where('id', $existing['id'])
                ->update(['state' => $newState, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            static::db()->table('users_states')->insert([
                'bot_id' => $botId,
                'user_id' => $userId,
                'state' => $newState,
                'data' => $data ? json_encode($data) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
