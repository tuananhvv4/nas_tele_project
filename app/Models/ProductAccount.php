<?php

declare(strict_types=1);

namespace App\Models;

class ProductAccount extends BaseModel
{
    protected static string $table = 'product_accounts';

    public static function forProduct(int $productId, int $perPage = 50, int $page = 1): array
    {
        return static::db()
            ->table(static::$table)
            ->where('product_id', $productId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function countByStatus(int $productId): array
    {
        $rows = static::db()->query(
            'SELECT status, COUNT(*) as cnt FROM product_accounts WHERE product_id = ? GROUP BY status',
            [$productId]
        );
        $result = ['available' => 0, 'used' => 0];
        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['cnt'];
        }
        return $result;
    }

    public static function bulkCreate(int $productId, array $lines): int
    {
        $count = 0;
        $now   = date('Y-m-d H:i:s');
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            static::db()->table(static::$table)->insert([
                'product_id' => $productId,
                'value'      => $line,
                'status'     => 'available',
                'created_at' => $now,
            ]);
            $count++;
        }
        return $count;
    }

    public static function deleteAvailable(int $productId): int
    {
        return static::db()
            ->table(static::$table)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->delete();
    }
}
