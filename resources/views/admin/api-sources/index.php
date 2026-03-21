<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Nguồn API</h1>
        <a href="/admin/api-sources/create?bot_id=<?= $botId ?>" class="btn btn-primary">+ Thêm nguồn API</a>
    </div>

    <!-- Bot selector -->
    <div class="card p-4">
        <form method="GET" class="flex gap-3 items-end">
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
                        <th>Tên</th>
                        <th>URL</th>
                        <th>Đồng bộ lần cuối</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sources['data'])): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">Chưa có nguồn API nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($sources['data'] as $src): ?>
                    <tr>
                        <td><?= $src['id'] ?></td>
                        <td class="font-medium"><?= htmlspecialchars($src['name']) ?></td>
                        <td class="text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($src['url']) ?></td>
                        <td class="text-sm text-gray-500"><?= $src['last_synced_at'] ? date('d/m/Y H:i', strtotime($src['last_synced_at'])) : 'Chưa đồng bộ' ?></td>
                        <td>
                            <span class="badge <?= $src['is_active'] ? 'badge-green' : 'badge-gray' ?>">
                                <?= $src['is_active'] ? 'Bật' : 'Tắt' ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/api-sources/<?= $src['id'] ?>/edit" class="btn btn-sm btn-secondary">Sửa</a>
                                <form method="POST" action="/admin/api-sources/<?= $src['id'] ?>/sync">
                                    <?= \Core\View::csrfField() ?>
                                    <button class="btn btn-sm btn-primary" type="submit">Đồng bộ</button>
                                </form>
                                <form method="POST" action="/admin/api-sources/<?= $src['id'] ?>" onsubmit="return confirm('Xoá nguồn API này?')">
                                    <?= \Core\View::csrfField() ?>
                                    <?= \Core\View::methodField('DELETE') ?>
                                    <input type="hidden" name="bot_id" value="<?= $botId ?>">
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
    </div>
</div>
