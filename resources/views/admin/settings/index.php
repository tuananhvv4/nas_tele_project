<?php $layout = 'admin'; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Cài đặt Bot</h1>
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

    <?php if ($botId): ?>
    <div class="card">
        <div class="card-header font-semibold text-gray-700">Cài đặt Bot</div>
        <div class="card-body">
            <form method="POST" action="/admin/settings">
                <?= \Core\View::csrfField() ?>
                <input type="hidden" name="bot_id" value="<?= $botId ?>">

                <!-- Default settings -->
                <?php
                $defaultSettings = [
                    'shop_name'         => ['label' => 'Tên cửa hàng', 'type' => 'text', 'default' => ''],
                    'welcome_message'   => ['label' => 'Tin nhắn chào mừng', 'type' => 'textarea', 'default' => 'Chào mừng bạn đến cửa hàng!'],
                    'currency'          => ['label' => 'Đơn vị tiền tệ', 'type' => 'text', 'default' => 'VNĐ'],
                    'support_contact'   => ['label' => 'Liên hệ hỗ trợ', 'type' => 'text', 'default' => ''],
                    'order_notify_chat' => ['label' => 'Chat ID nhận thông báo đơn hàng', 'type' => 'text', 'default' => ''],
                    'shipping_fee'      => ['label' => 'Phí giao hàng (đ)', 'type' => 'number', 'default' => '0'],
                    'free_ship_from'    => ['label' => 'Miễn phí ship từ (đ)', 'type' => 'number', 'default' => '0'],
                ];
                ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <?php foreach ($defaultSettings as $key => $conf): ?>
                <?php $val = $settings[$key]['value'] ?? $conf['default']; ?>
                <?php if ($conf['type'] === 'textarea'): ?>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($conf['label']) ?></label>
                    <textarea name="settings[<?= $key ?>]" class="form-textarea" rows="3"><?= htmlspecialchars($val) ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <?php else: ?>
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($conf['label']) ?></label>
                    <?php if ($conf['type'] === 'number'): ?>
                    <input type="number" name="settings[<?= $key ?>]" class="form-input" value="<?= htmlspecialchars($val) ?>">
                    <?php else: ?>
                    <input type="text" name="settings[<?= $key ?>]" class="form-input" value="<?= htmlspecialchars($val) ?>">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="card p-8 text-center text-gray-400">Chọn bot để xem cài đặt</div>
    <?php endif; ?>
</div>
