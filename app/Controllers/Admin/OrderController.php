<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Order;
use Core\Request;

class OrderController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $page  = $this->currentPage();
        $filters = ['status' => $request->string('status')];
        $orders = $botId ? Order::paginateForBot($botId, 20, $page, $filters) : ['data' => [], 'total' => 0, 'pages' => 0];

        $this->render('admin/orders/index', [
            'title'    => 'Đơn hàng',
            'bots'     => $bots,
            'botId'    => $botId,
            'orders'   => $orders,
            'statuses' => Order::STATUSES,
            'filters'  => $filters,
        ]);
    }

    public function show(Request $request, int $id): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $order = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Order::findWithItemsForBot($id, $b['id']);
            if ($found) { $order = $found; $botId = $b['id']; break; }
        }
        if (!$order) $this->abort(404);

        $this->render('admin/orders/show', [
            'title'    => 'Chi tiết đơn #' . $order['order_number'],
            'order'    => $order,
            'statuses' => Order::STATUSES,
            'botId'    => $botId,
        ]);
    }

    public function updateStatus(Request $request, int $id): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = 0;
        foreach ($bots as $b) {
            if (Order::findWithItemsForBot($id, $b['id'])) { $botId = $b['id']; break; }
        }
        if (!$botId) $this->abort(404);

        $status = $request->string('status');
        if (!array_key_exists($status, Order::STATUSES)) {
            $this->backWithError('Trạng thái không hợp lệ.');
            return;
        }

        Order::update($id, ['status' => $status]);
        $this->backWithSuccess('Đã cập nhật trạng thái đơn hàng.');
    }
}
