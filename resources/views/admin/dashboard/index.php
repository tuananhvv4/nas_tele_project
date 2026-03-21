<?php $layout = 'admin'; ?>

<div class="space-y-6">

    <!-- Bot selector -->
    <?php if (!empty($bots) && count($bots) > 1): ?>
    <div class="card p-4">
        <form method="GET" class="flex gap-3 items-end">
            <div>
                <label class="form-label text-xs">Bot</label>
                <select name="bot_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($bots as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == ($botId ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue-50">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Người dùng</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_users'] ?? 0) ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green-50">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Đơn hàng hôm nay</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['orders_today'] ?? 0) ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-yellow-50">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Doanh thu hôm nay</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['revenue_today'] ?? 0) ?>đ</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple-50">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Sản phẩm</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_products'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Recent orders -->
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-gray-700">Đơn hàng gần đây</h2>
            <a href="/admin/orders" class="text-sm text-blue-600 hover:underline">Xem tất cả →</a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">Chưa có đơn hàng nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td class="font-medium">#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['first_name'] ?? 'Unknown') ?></td>
                        <td><?= number_format((float)$order['total_amount']) ?>đ</td>
                        <td><?php
                            $badges = [
                                'pending'    => 'badge-yellow',
                                'confirmed'  => 'badge-blue',
                                'processing' => 'badge-blue',
                                'shipped'    => 'badge-blue',
                                'completed'  => 'badge-green',
                                'cancelled'  => 'badge-red',
                            ];
                            $labels = [
                                'pending'    => 'Chờ xử lý',
                                'confirmed'  => 'Đã xác nhận',
                                'processing' => 'Đang xử lý',
                                'shipped'    => 'Đang giao',
                                'completed'  => 'Hoàn thành',
                                'cancelled'  => 'Đã huỷ',
                            ];
                            $s = $order['status'];
                            ?>
                            <span class="<?= $badges[$s] ?? 'badge-gray' ?>"><?= $labels[$s] ?? $s ?></span>
                        </td>
                        <td class="text-gray-400"><?= date('d/m H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
