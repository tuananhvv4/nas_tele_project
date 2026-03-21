<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/promotions?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa mã: <?= htmlspecialchars($promo['code']) ?></h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin mã khuyến mãi</div>
        <div class="card-body">
            <form method="POST" action="/admin/promotions/<?= $promo['id'] ?>">
                <?= \Core\View::csrfField() ?>
                <?= \Core\View::methodField('PUT') ?>

                <div class="form-group">
                    <label class="form-label">Mã khuyến mãi</label>
                    <input type="text" class="form-input font-mono bg-gray-50" value="<?= htmlspecialchars($promo['code']) ?>" disabled>
                    <p class="form-hint">Không thể thay đổi mã sau khi tạo</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Loại giảm giá</label>
                        <select name="type" class="form-select">
                            <option value="percent" <?= (\Core\View::old('type', $promo['type'])) === 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                            <option value="fixed" <?= (\Core\View::old('type', $promo['type'])) === 'fixed' ? 'selected' : '' ?>>Số tiền cố định (đ)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá trị</label>
                        <input type="number" name="value" class="form-input" value="<?= \Core\View::old('value', $promo['value']) ?>" min="0" step="0.01">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Đơn hàng tối thiểu (đ)</label>
                        <input type="number" name="min_order" class="form-input" value="<?= \Core\View::old('min_order', $promo['min_order']) ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giới hạn sử dụng</label>
                        <input type="number" name="max_uses" class="form-input" value="<?= \Core\View::old('max_uses', $promo['max_uses']) ?>" min="0">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Bắt đầu</label>
                        <input type="datetime-local" name="start_at" class="form-input" value="<?= \Core\View::old('start_at', $promo['start_at'] ? date('Y-m-d\TH:i', strtotime($promo['start_at'])) : '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hết hạn</label>
                        <input type="datetime-local" name="end_at" class="form-input" value="<?= \Core\View::old('end_at', $promo['end_at'] ? date('Y-m-d\TH:i', strtotime($promo['end_at'])) : '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <input type="text" name="description" class="form-input" value="<?= htmlspecialchars(\Core\View::old('description', $promo['description'] ?? '')) ?>">
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" value="active" <?= ($promo['status'] ?? '') === 'active' ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="form-label mb-0">Kích hoạt</span>
                    </label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="/admin/promotions?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
