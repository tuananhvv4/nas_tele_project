<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Order;
use Core\Request;

class DashboardController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);

        $stats        = $botId ? Order::statsForBot($botId) : [];
        $recentOrders = [];
        if ($botId) {
            $result       = Order::paginateForBot($botId, 5, 1);
            $recentOrders = $result['data'] ?? [];
        }

        $this->render('admin/dashboard/index', [
            'title'        => 'Dashboard',
            'bots'         => $bots,
            'botId'        => $botId,
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}
