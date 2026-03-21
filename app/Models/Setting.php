<?php

declare(strict_types=1);

namespace App\Models;

class Setting extends BaseModel
{
    protected static string $table = 'settings';

    public static function forBot(int $botId): array
    {
        $rows = static::db()->table('settings')->where('bot_id', $botId)->get();
        $map  = [];
        foreach ($rows as $row) $map[$row['key']] = $row;
        return $map;
    }

    public static function get(int $botId, string $key, mixed $default = null): mixed
    {
        $row = static::db()->table('settings')
            ->where('bot_id', $botId)->where('key', $key)->first();
        if (!$row) return $default;
        return match ($row['type']) {
            'boolean' => (bool) $row['value'],
            'integer' => (int)  $row['value'],
            'json'    => json_decode($row['value'], true),
            default   => $row['value'],
        };
    }

    public static function set(int $botId, string $key, mixed $value, string $type = 'string', string $label = ''): void
    {
        $strValue = is_array($value) ? json_encode($value) : (string) $value;
        $existing = static::db()->table('settings')
            ->where('bot_id', $botId)->where('key', $key)->first();

        if ($existing) {
            static::db()->table('settings')
                ->where('id', $existing['id'])
                ->update(['value' => $strValue, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            static::db()->table('settings')->insert([
                'bot_id'     => $botId,
                'key'        => $key,
                'value'      => $strValue,
                'type'       => $type,
                'label'      => $label,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function bulkSet(int $botId, array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($botId, $key, $value);
        }
    }
}
