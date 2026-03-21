<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Sản phẩm</h1>
        <a href="/admin/products/create?bot_id=<?= $botId ?>" class="btn btn-primary">+ Thêm sản phẩm</a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <?php if (count($bots) > 1): ?>
            <div>
                <label class="form-label text-xs">Bot</label>
                <select name="bot_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($bots as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="bot_id" value="<?= $botId ?>">
            <?php endif; ?>

            <div>
                <label class="form-label text-xs">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label text-xs">Tìm kiếm</label>
                <input type="text" name="search" class="form-input" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Tên sản phẩm...">
            </div>

            <button type="submit" class="btn btn-secondary">Tìm</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Giá KM</th>
                        <th>Kho</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products['data'])): ?>
                    <tr><td colspan="8" class="text-center text-gray-400 py-8">Chưa có sản phẩm nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($products['data'] as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td class="font-medium"><?= htmlspecialchars($product['name']) ?><br><span class="text-xs text-gray-400"><?= htmlspecialchars($product['sku'] ?? '') ?></span></td>
                        <td class="text-gray-500 text-sm"><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                        <td><?= number_format((float)$product['price']) ?>đ</td>
                        <td><?= $product['sale_price'] ? number_format((float)$product['sale_price']).'đ' : '—' ?></td>
                        <td><?= $product['stock'] == -1 ? '∞' : $product['stock'] ?></td>
                        <td>
                            <span class="badge <?= ($product['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ($product['status'] ?? '') === 'active' ? 'Hiện' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/products/<?= $product['id'] ?>/accounts" class="btn btn-sm btn-secondary">Tài khoản</a>
                                <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-secondary">Sửa</a>
                                <form method="POST" action="/admin/products/<?= $product['id'] ?>" onsubmit="return confirm('Xoá sản phẩm này?')">
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
        <?php if (!empty($products['last_page']) && $products['last_page'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php for ($i = 1; $i <= $products['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>&bot_id=<?= $botId ?>&category_id=<?= $filters['category_id'] ?? '' ?>&search=<?= urlencode($filters['search'] ?? '') ?>" class="page-item <?= $i === ($products['current_page'] ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
