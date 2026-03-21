<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Bot</h1>
        <a href="/admin/bots/create" class="btn btn-primary">+ Thêm Bot</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên Bot</th>
                        <th>Username</th>
                        <th>Webhook</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bots['data'])): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">Chưa có bot nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($bots['data'] as $bot): ?>
                    <tr>
                        <td><?= $bot['id'] ?></td>
                        <td class="font-medium"><?= htmlspecialchars($bot['name']) ?></td>
                        <td class="text-gray-500">@<?= htmlspecialchars($bot['bot_username'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $bot['webhook_status'] === 'active' ? 'badge-green' : 'badge-yellow' ?>">
                                <?= $bot['webhook_status'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= ($bot['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ($bot['status'] ?? '') === 'active' ? 'Hoạt động' : 'Tắt' ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/bots/<?= $bot['id'] ?>/edit" class="btn btn-sm btn-secondary">Sửa</a>
                                <form method="POST" action="/admin/bots/<?= $bot['id'] ?>/webhook">
                                    <?= \Core\View::csrfField() ?>
                                    <button class="btn btn-sm btn-secondary" type="submit">Webhook</button>
                                </form>
                                <form method="POST" action="/admin/bots/<?= $bot['id'] ?>" onsubmit="return confirm('Xoá bot này?')">
                                    <?= \Core\View::csrfField() ?>
                                    <?= \Core\View::methodField('DELETE') ?>
                                    <button class="btn btn-sm btn-danger" type="submit">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($bots['last_page']) && $bots['last_page'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php for ($i = 1; $i <= $bots['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-item <?= $i === ($bots['current_page'] ?? 1) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
