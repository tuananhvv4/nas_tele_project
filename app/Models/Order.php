<?php

declare(strict_types=1);

namespace App\Models;

class Order extends BaseModel
{
    protected static string $table = 'orders';

    public const STATUSES = [
        'pending'    => 'Chờ xử lý',
        'confirmed'  => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'shipped'    => 'Đang giao',
        'completed'  => 'Hoàn thành',
        'cancelled'  => 'Đã huỷ',
    ];

    public static function paginateForBot(int $botId, int $perPage, int $page, array $filters = []): array
    {
        $q = static::db()
            ->table('orders o')
            ->select('o.*, tu.first_name, tu.username')
            ->join('telegram_users tu', 'tu.id', '=', 'o.telegram_user_id')
            ->where('o.bot_id', $botId);

        if (!empty($filters['status'])) $q = $q->where('o.status', $filters['status']);

        return $q->orderBy('o.id', 'DESC')->paginate($perPage, $page);
    }

    public static function findWithItemsForBot(int $id, int $botId): ?array
    {
        $order = static::db()->table('orders o')
            ->select('o.*, tu.first_name, tu.last_name, tu.username, tu.telegram_id')
            ->join('telegram_users tu', 'tu.id', '=', 'o.telegram_user_id')
            ->where('o.id', $id)
            ->where('o.bot_id', $botId)
            ->first();

        if (!$order) return null;

        $order['items'] = static::db()->table('order_items oi')
            ->select('oi.*, p.name as product_current_name')
            ->join('products p', 'p.id', '=', 'oi.product_id', 'LEFT')
            ->where('oi.order_id', $id)
            ->get();

        return $order;
    }

    public static function statsForBot(int $botId): array
    {
        $db = static::db();
        $today = date('Y-m-d');

        $totalUsers   = $db->table('telegram_users')->where('bot_id', $botId)->count();
        $ordersToday  = $db->table('orders')->where('bot_id', $botId)
            ->where('DATE(created_at)', $today)->count();
        $revenueToday = $db->query(
            "SELECT COALESCE(SUM(total_amount),0) as rev FROM orders WHERE bot_id=:b AND DATE(created_at)=:d AND status!='cancelled'",
            ['b' => $botId, 'd' => $today]
        )[0]['rev'] ?? 0;
        $totalProducts = $db->table('products')->where('bot_id', $botId)->count();

        return [
            'total_users'    => $totalUsers,
            'orders_today'   => $ordersToday,
            'revenue_today'  => $revenueToday,
            'total_products' => $totalProducts,
        ];
    }
}
