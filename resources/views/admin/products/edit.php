<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/products?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa sản phẩm</h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin sản phẩm</div>
        <div class="card-body">
            <form method="POST" action="/admin/products/<?= $product['id'] ?>">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <div class="form-group">
                    <label class="form-label">Tên sản phẩm <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars(\Core\View::old('name', $product['name'])) ?>" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" class="form-select">
                            <option value="">— Không có —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (\Core\View::old('category_id', $product['category_id']) == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input" value="<?= htmlspecialchars(\Core\View::old('sku', $product['sku'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars(\Core\View::old('description', $product['description'] ?? '')) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Giá <span class="text-red-500">*</span></label>
                        <input type="number" name="price" class="form-input" value="<?= \Core\View::old('price', $product['price']) ?>" min="0" step="1000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" class="form-input" value="<?= \Core\View::old('sale_price', $product['sale_price'] ?? '') ?>" min="0" step="1000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tồn kho</label>
                        <input type="number" name="stock" class="form-input" value="<?= \Core\View::old('stock', $product['stock']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="form-label mb-0">Hiển thị sản phẩm</span>
                    </label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="/admin/products?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
