<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\BroadcastLog;
use App\Models\TelegramUser;
use Core\Request;

class BroadcastController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $page  = $this->currentPage();
        $logs  = $botId ? BroadcastLog::paginateForBot($botId, 20, $page) : ['data' => [], 'total' => 0, 'pages' => 0];

        $this->render('admin/broadcast/index', [
            'title' => 'Broadcast',
            'bots'  => $bots,
            'botId' => $botId,
            'logs'  => $logs,
        ]);
    }

    public function create(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);

        $this->render('admin/broadcast/create', [
            'title' => 'Gửi broadcast',
            'bots'  => $bots,
            'botId' => $botId,
        ]);
    }

    public function send(Request $request): void
    {
        $user  = $this->authUser();
        $botId = $request->int('bot_id');
        $bot   = Bot::findForAdmin($botId, $user['id']);
        if (!$bot) { $this->backWithError('Bot không hợp lệ.'); return; }

        $message = trim($request->string('message'));
        if (!$message) { $this->backWithError('Nội dung tin nhắn không được trống.'); return; }

        // Create log entry, actual sending should be done via a queue/service
        $logId = BroadcastLog::create([
            'bot_id'     => $botId,
            'message'    => $message,
            'sent_count' => 0,
            'fail_count' => 0,
            'status'     => 'pending',
        ]);

        // TODO: dispatch a background job or send synchronously via BroadcastService
        $this->redirectWithSuccess("/admin/broadcast?bot_id=$botId", 'Broadcast đã được tạo và đang xử lý.');
    }

    public function logs(Request $request): void
    {
        $this->index($request);
    }
}
