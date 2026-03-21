<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Người dùng Telegram</h1>
    </div>

    <!-- Bot selector -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label text-xs">Bot</label>
                <select name="bot_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($bots as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Telegram ID</th>
                        <th>Tên</th>
                        <th>Username</th>
                        <th>Ngôn ngữ</th>
                        <th>Trạng thái</th>
                        <th>Ngày tham gia</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users['data'])): ?>
                    <tr><td colspan="8" class="text-center text-gray-400 py-8">Chưa có người dùng nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($users['data'] as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td class="font-mono text-sm"><?= $u['telegram_id'] ?></td>
                        <td><?= htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?></td>
                        <td class="text-gray-500"><?= $u['username'] ? '@'.htmlspecialchars($u['username']) : '—' ?></td>
                        <td><?= $u['language'] ?? '—' ?></td>
                        <td>
                            <span class="badge <?= $u['is_banned'] ? 'badge-red' : 'badge-green' ?>">
                                <?= $u['is_banned'] ? 'Bị chặn' : 'Hoạt động' ?>
                            </span>
                        </td>
                        <td class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/users/<?= $u['id'] ?>" class="btn btn-sm btn-secondary">Chi tiết</a>
                                <form method="POST" action="/admin/users/<?= $u['id'] ?>/ban">
                                    <?= \Core\View::csrfField() ?>
                                    <input type="hidden" name="bot_id" value="<?= $botId ?>">
                                    <button class="btn btn-sm <?= $u['is_banned'] ? 'btn-success' : 'btn-warning' ?>" type="submit">
                                        <?= $u['is_banned'] ? 'Bỏ chặn' : 'Chặn' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($users['last_page']) && $users['last_page'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php for ($i = 1; $i <= $users['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>&bot_id=<?= $botId ?>" class="page-item <?= $i === ($users['current_page'] ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
