<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/api-sources?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Thêm nguồn API</h1>
    </div>

    <div class="card max-w-xl">
        <form method="POST" action="/admin/api-sources">
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
                <label class="form-label">Tên nguồn <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input" value="<?= \Core\View::old('name') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">URL <span class="text-red-500">*</span></label>
                <input type="url" name="url" class="form-input" value="<?= \Core\View::old('url') ?>" required placeholder="https://api.example.com/products">
            </div>

            <div class="form-group">
                <label class="form-label">Headers <span class="text-gray-400 text-sm">(JSON, tuỳ chọn)</span></label>
                <textarea name="headers" class="form-textarea font-mono text-sm" rows="4" placeholder='{"Authorization": "Bearer token", "Accept": "application/json"}'><?= htmlspecialchars(\Core\View::old('headers')) ?></textarea>
                <p class="form-hint">Để trống nếu không cần headers</p>
            </div>

            <div class="form-group">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
                    <span class="form-label mb-0">Kích hoạt</span>
                </label>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Thêm nguồn API</button>
                <a href="/admin/api-sources?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
