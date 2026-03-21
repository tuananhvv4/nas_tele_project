<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/products?bot_id=<?= $product['bot_id'] ?>" class="text-sm text-gray-500 hover:underline">← Quay lại sản phẩm</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">Tài khoản: <?= htmlspecialchars($product['name']) ?></h1>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-green-600"><?= $counts['available'] ?></div>
            <div class="text-sm text-gray-500 mt-1">Còn lại</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-gray-500"><?= $counts['used'] ?></div>
            <div class="text-sm text-gray-500 mt-1">Đã sử dụng</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-blue-600"><?= $counts['available'] + $counts['used'] ?></div>
            <div class="text-sm text-gray-500 mt-1">Tổng cộng</div>
        </div>
    </div>

    <!-- Add accounts form -->
    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thêm tài khoản</div>
        <div class="card-body">
            <form method="POST" action="/admin/products/<?= $product['id'] ?>/accounts">
                <?= \Core\View::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Danh sách tài khoản <span class="text-gray-400 text-xs">(mỗi dòng một tài khoản, ví dụ: user:pass)</span></label>
                    <textarea name="values" class="form-textarea" rows="6"
                        placeholder="username1:password1&#10;username2:password2&#10;..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Thêm tài khoản</button>
            </form>
        </div>
    </div>

    <!-- Accounts list -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <span class="font-semibold text-gray-700">Danh sách tài khoản</span>
            <?php if ($counts['available'] > 0): ?>
            <form method="POST" action="/admin/products/<?= $product['id'] ?>/accounts/reset"
                  onsubmit="return confirm('Xoá hết <?= $counts['available'] ?> tài khoản chưa sử dụng?')">
                <?= \Core\View::csrfField() ?>
                <button type="submit" class="btn btn-sm btn-danger">Xoá hết chưa dùng</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Giá trị tài khoản</th>
                        <th>Trạng thái</th>
                        <th>Ngày thêm</th>
                        <th>Ngày dùng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts['data'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-8">Chưa có tài khoản nào</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($accounts['data'] as $acc): ?>
                    <tr>
                        <td><?= $acc['id'] ?></td>
                        <td class="font-mono text-sm">
                            <?php if ($acc['status'] === 'used'): ?>
                                <span class="text-gray-400"><?= htmlspecialchars($acc['value']) ?></span>
                            <?php else: ?>
                                <?= htmlspecialchars($acc['value']) ?>
                            <?php endif; ?>
                            <?php if ($acc['note']): ?>
                                <span class="text-xs text-gray-400 block"><?= htmlspecialchars($acc['note']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $acc['status'] === 'available' ? 'badge-green' : 'badge-gray' ?>">
                                <?= $acc['status'] === 'available' ? 'Còn lại' : 'Đã dùng' ?>
                            </span>
                        </td>
                        <td class="text-sm text-gray-500"><?= $acc['created_at'] ?></td>
                        <td class="text-sm text-gray-500"><?= $acc['used_at'] ?? '—' ?></td>
                        <td>
                            <?php if ($acc['status'] === 'available'): ?>
                            <form method="POST" action="/admin/products/<?= $product['id'] ?>/accounts/<?= $acc['id'] ?>"
                                  onsubmit="return confirm('Xoá tài khoản này?')">
                                <?= \Core\View::csrfField() ?>
                                <?= \Core\View::methodField('DELETE') ?>
                                <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
                            </form>
                            <?php else: ?>
                            <span class="text-gray-300 text-xs">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($accounts['last_page']) && $accounts['last_page'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php for ($i = 1; $i <= $accounts['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-item <?= $i === ($accounts['current_page'] ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
