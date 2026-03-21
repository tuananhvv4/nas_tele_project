<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/bots" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Thêm Bot mới</h1>
    </div>

    <div class="card">
        <div class="card-header font-semibold text-gray-700">Thông tin Bot</div>
        <div class="card-body">
            <form method="POST" action="/admin/bots">
                <?= \Core\View::csrfField() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="form-group">
                        <label class="form-label">Tên Bot <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="form-input" value="<?= \Core\View::old('name') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
                            <input type="text" name="bot_username" class="form-input pl-7" value="<?= \Core\View::old('bot_username') ?>" placeholder="mybotname">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Bot Token <span class="text-red-500">*</span></label>
                    <input type="text" name="bot_token" class="form-input font-mono text-sm" value="<?= \Core\View::old('bot_token') ?>" placeholder="123456:ABC-DEF..." required>
                    <p class="form-hint">Lấy token từ @BotFather trên Telegram</p>
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" value="active" checked class="w-4 h-4">
                        <span class="form-label mb-0">Kích hoạt bot</span>
                    </label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Thêm Bot</button>
                    <a href="/admin/bots" class="btn btn-secondary">Huỷ</a>
                </div>
            </form>
        </div>
    </div>
</div>
