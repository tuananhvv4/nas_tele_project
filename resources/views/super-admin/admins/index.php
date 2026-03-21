<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Danh sách Admins</h2>
            <p class="text-sm text-gray-500 mt-0.5">Quản lý tài khoản admin và quyền truy cập</p>
        </div>
        <a href="/super-admin/admins/create" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tạo Admin mới
        </a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên đăng nhập</th>
                        <th>Tên hiển thị</th>
                        <th>Email</th>
                        <th>Max Bots</th>
                        <th>Trạng thái</th>
                        <th>Tạo lúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($admins)): ?>
                    <tr><td colspan="8" class="text-center text-gray-400 py-8">Chưa có admin nào</td></tr>
                <?php else: ?>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= $admin['id'] ?></td>
                    <td class="font-medium"><?= htmlspecialchars($admin['username']) ?></td>
                    <td><?= htmlspecialchars($admin['display_name'] ?? '') ?></td>
                    <td class="text-gray-400"><?= htmlspecialchars($admin['email'] ?? '—') ?></td>
                    <td class="text-center"><?= $admin['max_bots'] ?></td>
                    <td>
                        <?php if ($admin['status'] === 'active'): ?>
                            <span class="badge-green">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge-red">Vô hiệu</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-gray-400 text-xs"><?= date('d/m/Y', strtotime($admin['created_at'])) ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="/super-admin/admins/<?= $admin['id'] ?>/features" class="text-purple-600 hover:text-purple-800 text-xs font-medium">Phân quyền</a>
                            <a href="/super-admin/admins/<?= $admin['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Sửa</a>
                            <form method="POST" action="/super-admin/admins/<?= $admin['id'] ?>"
                                onsubmit="return confirm('Xoá admin này?')" class="inline">
                                <?= \Core\View::csrfField() ?>
                                <?= \Core\View::methodField('DELETE') ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Xoá</button>
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
