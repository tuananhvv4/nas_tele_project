<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="/admin/users?bot_id=<?= $botId ?>" class="text-gray-500 hover:text-gray-700">← Quay lại</a>
        <h1 class="text-2xl font-bold text-gray-800">Chi tiết người dùng</h1>
    </div>

    <div class="card max-w-lg">
        <dl class="divide-y divide-gray-100">
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Telegram ID</dt>
                <dd class="text-sm font-mono"><?= $tgUser['telegram_id'] ?></dd>
            </div>
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Họ tên</dt>
                <dd class="text-sm"><?= htmlspecialchars(trim(($tgUser['first_name'] ?? '') . ' ' . ($tgUser['last_name'] ?? ''))) ?></dd>
            </div>
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Username</dt>
                <dd class="text-sm"><?= $tgUser['username'] ? '@'.htmlspecialchars($tgUser['username']) : '—' ?></dd>
            </div>
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Ngôn ngữ</dt>
                <dd class="text-sm"><?= htmlspecialchars($tgUser['language'] ?? '—') ?></dd>
            </div>
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Trạng thái</dt>
                <dd>
                    <span class="badge <?= $tgUser['is_banned'] ? 'badge-red' : 'badge-green' ?>">
                        <?= $tgUser['is_banned'] ? 'Bị chặn' : 'Hoạt động' ?>
                    </span>
                </dd>
            </div>
            <div class="py-3 flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Ngày tham gia</dt>
                <dd class="text-sm"><?= date('d/m/Y H:i', strtotime($tgUser['created_at'])) ?></dd>
            </div>
        </dl>

        <div class="mt-6 flex gap-3">
            <form method="POST" action="/admin/users/<?= $tgUser['id'] ?>/ban">
                <?= \Core\View::csrfField() ?>
                <input type="hidden" name="bot_id" value="<?= $botId ?>">
                <button class="btn <?= $tgUser['is_banned'] ? 'btn-success' : 'btn-danger' ?>" type="submit">
                    <?= $tgUser['is_banned'] ? 'Bỏ chặn người dùng' : 'Chặn người dùng' ?>
                </button>
            </form>
            <a href="/admin/users?bot_id=<?= $botId ?>" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>
