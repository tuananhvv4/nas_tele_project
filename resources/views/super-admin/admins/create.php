<?php $layout = 'admin'; ?>

<div>
    <div class="mb-6">
        <a href="/super-admin/admins" class="text-sm text-blue-600 hover:underline">← Quay lại</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold text-gray-700">Tạo Admin mới</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/super-admin/admins" class="space-y-4">
                <?= \Core\View::csrfField() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Tên đăng nhập <span class="text-red-500">*</span></label>
                        <input type="text" name="username" class="form-input" required minlength="3"
                            value="<?= \Core\View::old('username') ?>" placeholder="admin_shop1">
                    </div>
                    <div>
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" name="display_name" class="form-input"
                            value="<?= \Core\View::old('display_name') ?>" placeholder="Nguyễn Văn A">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input"
                            value="<?= \Core\View::old('email') ?>" placeholder="admin@example.com">
                    </div>
                    <div>
                        <label class="form-label">Mật khẩu <span class="text-red-500">*</span></label>
                        <input type="password" name="password" class="form-input" required minlength="8"
                            placeholder="Tối thiểu 8 ký tự">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label class="form-label">Số bot tối đa</label>
                        <input type="number" name="max_bots" class="form-input" min="1" max="50"
                            value="<?= \Core\View::old('max_bots', '1') ?>">
                        <p class="form-hint">Giới hạn số lượng bot admin này có thể tạo</p>
                    </div>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="btn-primary">Tạo Admin</button>
                    <a href="/super-admin/admins" class="btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
