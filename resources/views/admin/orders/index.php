<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Đơn hàng</h1>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label text-xs">Bot</label>
                <select name="bot_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($bots as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label text-xs">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Lọc</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders['data'])): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">Chưa có đơn hàng nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($orders['data'] as $order): ?>
                    <?php $statusColors = ['pending'=>'badge-yellow','confirmed'=>'badge-blue','processing'=>'badge-blue','shipped'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red']; ?>
                    <tr>
                        <td class="font-medium">#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['first_name'] ?? '—') ?></td>
                        <td><?= number_format((float)($order['total_amount'] ?? 0)) ?>đ</td>
                        <td>
                            <span class="badge <?= $statusColors[$order['status']] ?? 'badge-gray' ?>">
                                <?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?>
                            </span>
                        </td>
                        <td class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="/admin/orders/<?= $order['id'] ?>?bot_id=<?= $botId ?>" class="btn btn-sm btn-secondary">Xem</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($orders['last_page']) && $orders['last_page'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php for ($i = 1; $i <= $orders['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>&bot_id=<?= $botId ?>&status=<?= urlencode($filters['status'] ?? '') ?>" class="page-item <?= $i === ($orders['current_page'] ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
