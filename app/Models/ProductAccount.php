<?php

declare(strict_types=1);

namespace App\Models;

class ProductAccount extends BaseModel
{
    protected static string $table = 'product_accounts';

    public static function forProduct($productId, $perPage = 50, $page = 1): array
    {
        $productId = (int) $productId;
        $perPage   = (int) $perPage;
        $page      = (int) $page;
        return static::db()
            ->table(static::$table)
            ->where('product_id', $productId)
            ->orderBy('id', 'DESC')
            ->paginate($perPage, $page);
    }

    public static function countByStatus($productId): array
    {
        $productId = (int) $productId;
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

    public static function bulkCreate($productId, array $lines): int
    {
        $productId = (int) $productId;
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

    public static function deleteAvailable($productId): int
    {
        $productId = (int) $productId;
        return static::db()
            ->table(static::$table)
            ->where('product_id', $productId)
            ->where('status', 'available')
            ->delete();
    }
}
