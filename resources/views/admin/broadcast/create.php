<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/broadcast?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Gửi tin nhắn broadcast</h1>
    </div>

    <div class="card max-w-xl">
        <form method="POST" action="/admin/broadcast/send">
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
                <label class="form-label">Nội dung tin nhắn <span class="text-red-500">*</span></label>
                <textarea name="message" class="form-textarea" rows="6" required placeholder="Nội dung tin nhắn gửi đến tất cả users..."><?= htmlspecialchars(\Core\View::old('message')) ?></textarea>
                <p class="form-hint">Hỗ trợ định dạng Markdown cho Telegram (bold, italic, code, ...)</p>
            </div>

            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800 mb-4">
                <strong>⚠ Lưu ý:</strong> Tin nhắn sẽ được gửi đến TẤT CẢ người dùng của bot này. Hãy chắc chắn trước khi gửi.
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Bạn có chắc muốn gửi broadcast đến tất cả người dùng?')">Gửi Broadcast</button>
                <a href="/admin/broadcast?bot_id=<?= $botId ?>" class="btn btn-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
