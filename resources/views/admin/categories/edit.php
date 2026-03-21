<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/categories?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa danh mục</h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin danh mục</div>
        <div class="card-body">
            <form method="POST" action="/admin/categories/<?= $category['id'] ?>">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Tên danh mục <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars(\Core\View::old('name', $category['name'])) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Danh mục cha</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— Không có —</option>
                            <?php foreach ($parents as $p): ?>
                            <?php if ($p['id'] === $category['id']) continue; ?>
                            <option value="<?= $p['id'] ?>" <?= (\Core\View::old('parent_id', $category['parent_id']) == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars(\Core\View::old('description', $category['description'] ?? '')) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Thứ tự sắp xếp</label>
                        <input type="number" name="sort_order" class="form-input" value="<?= \Core\View::old('sort_order', $category['sort_order']) ?>">
                    </div>
                    <div class="form-group flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="status" value="inactive">
                            <input type="checkbox" name="status" value="active" <?= ($category['status'] ?? '') === 'active' ? 'checked' : '' ?> class="w-4 h-4">
                            <span class="form-label mb-0">Hiển thị</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="/admin/categories?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
