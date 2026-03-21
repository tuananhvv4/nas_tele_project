<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Broadcast</h1>
        <a href="/admin/broadcast/create?bot_id=<?= $botId ?>" class="btn btn-primary">+ Gửi tin mới</a>
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
                        <th>Nội dung</th>
                        <th>Gửi thành công</th>
                        <th>Thất bại</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs['data'])): ?>
                    <tr><td colspan="6" class="text-center text-gray-400 py-8">Chưa có broadcast nào</td></tr>
                    <?php else: ?>
                    <?php foreach ($logs['data'] as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td class="max-w-xs truncate"><?= htmlspecialchars(mb_substr($log['message'] ?? '', 0, 80)) ?><?= mb_strlen($log['message'] ?? '') > 80 ? '...' : '' ?></td>
                        <td class="text-green-600"><?= $log['sent_count'] ?></td>
                        <td class="text-red-500"><?= $log['fail_count'] ?></td>
                        <td>
                            <?php $statusColors = ['pending'=>'badge-yellow','sending'=>'badge-blue','done'=>'badge-green','failed'=>'badge-red']; ?>
                            <span class="badge <?= $statusColors[$log['status']] ?? 'badge-gray' ?>"><?= $log['status'] ?></span>
                        </td>
                        <td class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
