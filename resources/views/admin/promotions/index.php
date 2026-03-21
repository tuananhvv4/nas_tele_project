<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Khuyến mãi</h1>
        <a href="/admin/promotions/create?bot_id=<?= $botId ?>" class="btn btn-primary">+ Thêm mã KM</a>
    </div>

    <!-- Bot selector -->
    <div class="card p-4">
        <form method="GET" class="flex gap-3 items-end">
            <div>
                <label class="form-label text-xs">Bot</label>
                <select name="bot_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($bots as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Đã dùng / Giới hạn</th>
                        <th>Hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promos['data'])): ?>
                    <tr><td colspan="7" class="text-center text-gray-400 py-8">Chưa có mã khuyến mãi nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($promos['data'] as $p): ?>
                    <tr>
                        <td class="font-mono font-bold"><?= htmlspecialchars($p['code']) ?></td>
                        <td><?= $p['type'] === 'percent' ? 'Phần trăm' : 'Số tiền' ?></td>
                        <td><?= $p['type'] === 'percent' ? $p['value'].'%' : number_format((float)$p['value']).'đ' ?></td>
                        <td><?= $p['used_count'] ?> / <?= $p['max_uses'] > 0 ? $p['max_uses'] : '∞' ?></td>
                        <td class="text-sm text-gray-500"><?= $p['end_at'] ? date('d/m/Y', strtotime($p['end_at'])) : '—' ?></td>
                        <td>
                        <span class="badge <?= ($p['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' ?>">
                            <?= ($p['status'] ?? '') === 'active' ? 'Bật' : 'Tắt' ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/promotions/<?= $p['id'] ?>/edit" class="btn btn-sm btn-secondary">Sửa</a>
                                <form method="POST" action="/admin/promotions/<?= $p['id'] ?>" onsubmit="return confirm('Xoá mã khuyến mãi này?')">
                                    <?= \Core\View::csrfField() ?>
                                    <?= \Core\View::methodField('DELETE') ?>
                                    <input type="hidden" name="bot_id" value="<?= $botId ?>">
                                    <button class="btn btn-sm btn-danger" type="submit">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
