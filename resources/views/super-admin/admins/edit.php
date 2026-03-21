<?php $layout = 'admin'; ?>

<div>
    <div class="mb-6">
        <a href="/super-admin/admins" class="text-sm text-blue-600 hover:underline">← Quay lại</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-gray-700">Sửa Admin: <?= htmlspecialchars($admin['username']) ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/super-admin/admins/<?= $admin['id'] ?>" class="space-y-4">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-input bg-gray-50 text-gray-400 cursor-not-allowed"
                            value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                        <p class="form-hint">Không thể thay đổi tên đăng nhập</p>
                    </div>
                    <div>
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" name="display_name" class="form-input"
                            value="<?= htmlspecialchars($admin['display_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input"
                            value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-input" minlength="8"
                            placeholder="Để trống nếu không đổi">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Số bot tối đa</label>
                        <input type="number" name="max_bots" class="form-input" min="1" max="50"
                            value="<?= $admin['max_bots'] ?>">
                    </div>
                    <div>
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $admin['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="inactive" <?= $admin['status'] === 'inactive' ? 'selected' : '' ?>>Vô hiệu</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">Lưu thay đổi</button>
                    <a href="/super-admin/admins" class="btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
