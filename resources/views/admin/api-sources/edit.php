<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/api-sources?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Sửa nguồn API: <?= htmlspecialchars($source['name']) ?></h1>
    </div>

    <div class="card max-w-xl">
        <form method="POST" action="/admin/api-sources/<?= $source['id'] ?>">
            <?= \Core\View::csrfField() ?>
            <?= \Core\View::methodField('PUT') ?>

            <div class="form-group">
                <label class="form-label">Tên nguồn <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars(\Core\View::old('name', $source['name'])) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">URL <span class="text-red-500">*</span></label>
                <input type="url" name="url" class="form-input" value="<?= htmlspecialchars(\Core\View::old('url', $source['url'])) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Headers <span class="text-gray-400 text-sm">(JSON, tuỳ chọn)</span></label>
                <textarea name="headers" class="form-textarea font-mono text-sm" rows="4"><?= htmlspecialchars(\Core\View::old('headers', $source['headers'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?= $source['is_active'] ? 'selected' : '' ?>>Kích hoạt</option>
                    <option value="0" <?= !$source['is_active'] ? 'selected' : '' ?>>Tắt</option>
                </select>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                <form method="POST" action="/admin/api-sources/<?= $source['id'] ?>/test" style="display:inline">
                    <?= \Core\View::csrfField() ?>
                    <button type="submit" class="btn btn-secondary">Test kết nối</button>
                </form>
                <a href="/admin/api-sources?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
