<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/bots" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa Bot: <?= htmlspecialchars($bot['name']) ?></h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin Bot</div>
        <div class="card-body">
            <form method="POST" action="/admin/bots/<?= $bot['id'] ?>">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Tên Bot <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars(\Core\View::old('name', $bot['name'])) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
                            <input type="text" name="bot_username" class="form-input pl-7" value="<?= htmlspecialchars(\Core\View::old('bot_username', $bot['bot_username'] ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Bot Token mới <span class="text-gray-400 text-sm">(để trống nếu không đổi)</span></label>
                        <input type="text" name="bot_token" class="form-input font-mono text-sm" placeholder="Nhập token mới nếu muốn thay đổi">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($bot['status'] ?? '') === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="inactive" <?= ($bot['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tắt</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="/admin/bots" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
