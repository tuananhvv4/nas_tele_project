<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\TelegramUser;
use Core\Request;

class UserController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $page  = $this->currentPage();
        $users = $botId ? TelegramUser::paginateForBot($botId, 20, $page) : ['data' => [], 'total' => 0, 'pages' => 0];

        $this->render('admin/users/index', [
            'title' => 'Người dùng Telegram',
            'bots'  => $bots,
            'botId' => $botId,
            'users' => $users,
        ]);
    }

    public function show(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        $tgUser = null; $botId = 0;
        foreach ($bots as $b) {
            $found = TelegramUser::findForBot($id, $b['id']);
            if ($found) { $tgUser = $found; $botId = $b['id']; break; }
        }
        if (!$tgUser) $this->abort(404);

        $this->render('admin/users/show', [
            'title'  => 'Chi tiết người dùng',
            'tgUser' => $tgUser,
            'botId'  => $botId,
        ]);
    }

    public function toggleBan(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        foreach ($bots as $b) {
            $tgUser = TelegramUser::findForBot($id, $b['id']);
            if ($tgUser) { TelegramUser::toggleBan($id); break; }
        }
        $botId = $request->int('bot_id');
        $this->redirectWithSuccess("/admin/users?bot_id=$botId", 'Đã cập nhật trạng thái.');
    }
}
