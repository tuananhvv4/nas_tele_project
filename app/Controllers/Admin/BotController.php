<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use Core\Request;
use Telegram\Bot\Api as TelegramApi;

class BotController extends BaseController
{
    public function index(): void
    {
        $user  = $this->authUser();
        $page  = $this->currentPage();
        $bots  = Bot::paginateForAdmin($user['id'], 15, $page);
        $this->render('admin/bots/index', ['title' => 'Quản lý Bot', 'bots' => $bots]);
    }

    public function create(): void
    {
        $this->render('admin/bots/create', ['title' => 'Thêm Bot']);
    }

    public function store(Request $request): void
    {
        $user  = $this->authUser();
        $name  = trim($request->string('name'));
        $token = trim($request->string('bot_token'));

        if (!$name || !$token) {
            $this->backWithError('Vui lòng điền đầy đủ thông tin.');
            return;
        }

        Bot::create([
            'admin_user_id'  => $user['id'],
            'name'           => $name,
            'bot_token'      => $token,
            'bot_username'   => $request->string('bot_username') ?: null,
            'webhook_status' => 'not_set',
            'status'         => 'active',
        ]);

        $this->redirectWithSuccess('/admin/bots', 'Thêm bot thành công!');
    }

    public function edit(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bot  = Bot::findForAdmin($id, $user['id']);
        if (!$bot) $this->abort(404);

        $this->render('admin/bots/edit', ['title' => 'Sửa Bot', 'bot' => $bot]);
    }

    public function update(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bot  = Bot::findForAdmin($id, $user['id']);
        if (!$bot) $this->abort(404);

        $data = ['name' => trim($request->string('name')), 'bot_username' => $request->string('bot_username') ?: null, 'status' => $request->string('status', 'active')];
        if ($t = trim($request->string('bot_token'))) $data['bot_token'] = $t;

        Bot::update($id, $data);
        $this->redirectWithSuccess('/admin/bots', 'Cập nhật bot thành công!');
    }

    public function destroy(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bot  = Bot::findForAdmin($id, $user['id']);
        if (!$bot) $this->abort(404);

        Bot::delete($id);
        $this->redirectWithSuccess('/admin/bots', 'Đã xoá bot.');
    }

    public function setWebhook(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bot  = Bot::findForAdmin($id, $user['id']);
        if (!$bot) $this->abort(404);

        $baseUrl    = rtrim($_ENV['APP_URL'] ?? '', '/');
        $webhookUrl = $baseUrl . '/webhook.php?bot_id=' . $id;

        try {
            $telegram = new TelegramApi($bot['bot_token']);
            $result   = $telegram->setWebhook(['url' => $webhookUrl]);

            if ($result) {
                Bot::update($id, [
                    'webhook_url'    => $webhookUrl,
                    'webhook_status' => 'active',
                ]);
                $this->backWithSuccess('Webhook đã được đăng ký thành công!');
            } else {
                $this->backWithError('Telegram không xác nhận webhook. Vui lòng thử lại.');
            }
        } catch (\Throwable $e) {
            $this->backWithError('Lỗi đăng ký webhook: ' . $e->getMessage());
        }
    }
}
