<?php $layout = 'admin'; ?>

<div>
    <div class="mb-6">
        <a href="/super-admin/admins" class="text-sm text-blue-600 hover:underline">← Quay lại</a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="font-semibold text-gray-700">Phân quyền tính năng</h2>
                <p class="text-sm text-gray-400 mt-0.5">Admin: <strong><?= htmlspecialchars($admin['username']) ?></strong></p>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="/super-admin/admins/<?= $admin['id'] ?>/features" class="space-y-5">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <!-- Feature toggles -->
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-gray-600">Tính năng được phép truy cập</p>
                    <?php foreach ($featureLabels as $key => $label): ?>
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="perm_<?= $key ?>"
                            class="w-4 h-4 rounded text-blue-600"
                            <?= !empty($perms[$key]) ? 'checked' : '' ?>>
                        <span class="text-sm text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Limits -->
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="form-label">Giới hạn sản phẩm</label>
                        <input type="number" name="max_products" class="form-input" min="0"
                            value="<?= (int)($perms['max_products'] ?? 100) ?>">
                        <p class="form-hint">0 = không giới hạn</p>
                    </div>
                    <div>
                        <label class="form-label">Số bot tối đa</label>
                        <input type="number" name="max_bots" class="form-input" min="1"
                            value="<?= (int)($perms['max_bots'] ?? 1) ?>">
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">Lưu phân quyền</button>
                    <a href="/super-admin/admins" class="btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
