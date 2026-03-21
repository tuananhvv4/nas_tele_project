<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Danh mục</h1>
        <a href="/admin/categories/create?bot_id=<?= $botId ?>" class="btn btn-primary">+ Thêm danh mục</a>
    </div>

    <!-- Bot selector -->
    <?php if (count($bots) > 1): ?>
    <div class="card p-4">
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-600">Bot:</label>
            <select name="bot_id" class="form-select w-auto" onchange="this.form.submit()">
                <?php foreach ($bots as $b): ?>
                <option value="<?= $b['id'] ?>" <?= $b['id'] == $botId ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên danh mục</th>
                        <th>Danh mục cha</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">Chưa có danh mục nào</td></tr>
                    <?php else: ?>
                    <?php
                    $catMap = [];
                    foreach ($categories as $c) $catMap[$c['id']] = $c['name'];
                    foreach ($categories as $cat):
                    ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td class="font-medium"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-gray-500"><?= $cat['parent_id'] ? htmlspecialchars($catMap[$cat['parent_id']] ?? '—') : '—' ?></td>
                        <td><?= $cat['sort_order'] ?></td>
                        <td>
                            <span class="badge <?= ($cat['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' ?>">
                                <?= ($cat['status'] ?? '') === 'active' ? 'Hiện' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="/admin/categories/<?= $cat['id'] ?>/edit" class="btn btn-sm btn-secondary">Sửa</a>
                                <form method="POST" action="/admin/categories/<?= $cat['id'] ?>" onsubmit="return confirm('Xoá danh mục này?')">
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
