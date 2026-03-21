<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/orders?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Đơn hàng #<?= $order['id'] ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <h2 class="font-semibold text-gray-700 mb-4">Sản phẩm</h2>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] ?? [] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= number_format((float)$item['price']) ?>đ</td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format((float)$item['subtotal']) ?>đ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold">
                                <td colspan="3" class="text-right">Tổng:</td>
                                <td><?= number_format((float)($order['total_amount'] ?? 0)) ?>đ</td>
                            </tr>
                            <?php if ($order['discount_amount'] ?? 0): ?>
                            <tr class="text-green-600">
                                <td colspan="3" class="text-right">Giảm giá:</td>
                                <td>-<?= number_format((float)$order['discount_amount']) ?>đ</td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php if (!empty($order['shipping_info'])): ?>
            <div class="card">
                <h2 class="font-semibold text-gray-700 mb-4">Thông tin giao hàng</h2>
                <?php $ship = is_string($order['shipping_info']) ? json_decode($order['shipping_info'], true) : $order['shipping_info']; ?>
                <?php if (is_array($ship)): foreach ($ship as $k => $v): ?>
                <div class="flex gap-2 py-1 text-sm">
                    <span class="text-gray-500 w-32"><?= htmlspecialchars($k) ?>:</span>
                    <span><?= htmlspecialchars((string)$v) ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Status panel -->
        <div class="space-y-4">
            <div class="card">
                <h2 class="font-semibold text-gray-700 mb-4">Cập nhật trạng thái</h2>
                <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status">
                    <?= \Core\View::csrfField() ?>
                    <?= \Core\View::methodField('PUT') ?>
                    <div class="form-group">
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Cập nhật</button>
                </form>
            </div>

            <div class="card">
                <h2 class="font-semibold text-gray-700 mb-4">Khách hàng</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tên</dt>
                        <dd><?= htmlspecialchars(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''))) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Username</dt>
                        <dd><?= $order['username'] ? '@'.htmlspecialchars($order['username']) : '—' ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Telegram ID</dt>
                        <dd class="font-mono"><?= $order['telegram_id'] ?? '—' ?></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
