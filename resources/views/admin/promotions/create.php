<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/promotions?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Thêm mã khuyến mãi</h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin mã khuyến mãi</div>
        <div class="card-body">
            <form method="POST" action="/admin/promotions">
                <?= \Core\View::csrfField() ?>
                <input type="hidden" name="bot_id" value="<?= $botId ?>">

                <?php if (count($bots) > 1): ?>
                <div class="form-group">
                    <label class="form-label">Bot <span class="text-red-500">*</span></label>
                    <select name="bot_id" class="form-select">
                        <?php foreach ($bots as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Mã khuyến mãi <span class="text-red-500">*</span></label>
                    <input type="text" name="code" class="form-input font-mono uppercase" value="<?= \Core\View::old('code') ?>" required placeholder="SUMMER20">
                    <p class="form-hint">Tự động chuyển thành chữ hoa</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Loại giảm giá</label>
                        <select name="type" class="form-select">
                            <option value="percent" <?= \Core\View::old('type') === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                            <option value="fixed" <?= \Core\View::old('type') === 'fixed' ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá trị <span class="text-red-500">*</span></label>
                        <input type="number" name="value" class="form-input" value="<?= \Core\View::old('value') ?>" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Đơn hàng tối thiểu (đ)</label>
                        <input type="number" name="min_order" class="form-input" value="<?= \Core\View::old('min_order', '0') ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giới hạn sử dụng <span class="text-gray-400 text-xs">(0 = không giới hạn)</span></label>
                        <input type="number" name="max_uses" class="form-input" value="<?= \Core\View::old('max_uses', '0') ?>" min="0">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Bắt đầu</label>
                        <input type="datetime-local" name="start_at" class="form-input" value="<?= \Core\View::old('start_at') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hết hạn</label>
                        <input type="datetime-local" name="end_at" class="form-input" value="<?= \Core\View::old('end_at') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input type="text" name="description" class="form-input" value="<?= \Core\View::old('description') ?>">
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" value="active" checked class="w-4 h-4">
                        <span class="form-label mb-0">Kích hoạt</span>
                    </label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Thêm mã KM</button>
                    <a href="/admin/promotions?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
