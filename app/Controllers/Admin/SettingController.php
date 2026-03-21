<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Setting;
use Core\Request;

class SettingController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $settings = $botId ? Setting::forBot($botId) : [];

        $this->render('admin/settings/index', [
            'title'    => 'Cài đặt',
            'bots'     => $bots,
            'botId'    => $botId,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): void
    {
        $user  = $this->authUser();
        $botId = $request->int('bot_id');
        $bot   = Bot::findForAdmin($botId, $user['id']);
        if (!$bot) { $this->backWithError('Bot không hợp lệ.'); return; }

        $data = $request->post('settings', []);
        if (is_array($data)) {
            Setting::bulkSet($botId, $data);
        }

        $this->redirectWithSuccess("/admin/settings?bot_id=$botId", 'Lưu cài đặt thành công!');
    }
}
