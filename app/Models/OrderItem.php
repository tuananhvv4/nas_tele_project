<?php

declare(strict_types=1);

namespace App\Models;

class OrderItem extends BaseModel
{
    protected static string $table = 'order_items';

    public static function forOrder(int $orderId): array
    {
        return static::db()->table('order_items oi')
            ->select('oi.*, p.name as product_current_name, p.price as product_current_price')
            ->join('products p', 'p.id', '=', 'oi.product_id', 'LEFT')
            ->where('oi.order_id', $orderId)
            ->get();
    }
}
