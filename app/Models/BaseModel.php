<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;

abstract class BaseModel
{
    protected static string $table  = '';
    protected static string $primaryKey = 'id';

    // ── Read ──────────────────────────────────────────────────────────────────

    public static function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        return static::db()
            ->table(static::$table)
            ->orderBy($orderBy, $direction)
            ->get();
    }

    public static function find(int|string $id): ?array
    {
        return static::db()
            ->table(static::$table)
            ->find($id, static::$primaryKey);
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return static::db()
            ->table(static::$table)
            ->where($column, $value)
            ->first();
    }

    public static function where(string $column, mixed $value, string $operator = '='): Database
    {
        return static::db()
            ->table(static::$table)
            ->where($column, $value, $operator);
    }

    public static function count(): int
    {
        return static::db()->table(static::$table)->count();
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        return static::db()
            ->table(static::$table)
            ->orderBy(static::$primaryKey, 'DESC')
            ->paginate($perPage, $page);
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    public static function create(array $data): int
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return static::db()->table(static::$table)->insert($data);
    }

    public static function update(int|string $id, array $data): int
    {
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return static::db()
            ->table(static::$table)
            ->where(static::$primaryKey, $id)
            ->update($data);
    }

    public static function delete(int|string $id): int
    {
        return static::db()
            ->table(static::$table)
            ->where(static::$primaryKey, $id)
            ->delete();
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    protected static function db(): Database
    {
        return Database::getInstance();
    }
}
