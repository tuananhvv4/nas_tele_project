<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Promotion;
use Core\Request;

class PromotionController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $page  = $this->currentPage();
        $promos = $botId ? Promotion::paginateForBot($botId, 20, $page) : ['data' => [], 'total' => 0, 'pages' => 0];

        $this->render('admin/promotions/index', [
            'title'  => 'Khuyến mãi',
            'bots'   => $bots,
            'botId'  => $botId,
            'promos' => $promos,
        ]);
    }

    public function create(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);

        $this->render('admin/promotions/create', [
            'title' => 'Thêm khuyến mãi',
            'bots'  => $bots,
            'botId' => $botId,
        ]);
    }

    public function store(Request $request): void
    {
        $user  = $this->authUser();
        $botId = $request->int('bot_id');
        $bot   = Bot::findForAdmin($botId, $user['id']);
        if (!$bot) { $this->backWithError('Bot không hợp lệ.'); return; }

        $code = strtoupper(trim($request->string('code')));
        if (!$code) { $this->backWithError('Mã khuyến mãi không được trống.'); return; }

        if (Promotion::findByCode($botId, $code)) {
            $this->backWithError('Mã khuyến mãi này đã tồn tại.');
            return;
        }

        Promotion::create([
            'bot_id'        => $botId,
            'code'          => $code,
            'type'          => $request->string('type', 'percent'),
            'value'         => (float) $request->string('value'),
            'min_order'     => (float) $request->string('min_order', '0'),
            'max_uses'      => $request->int('max_uses', 0),
            'start_at'      => $request->string('start_at') ?: null,
            'end_at'        => $request->string('end_at') ?: null,
            'status' => $request->string('status', 'active'),
            'description'   => $request->string('description'),
        ]);

        $this->redirectWithSuccess("/admin/promotions?bot_id=$botId", 'Thêm khuyến mãi thành công!');
    }

    public function edit(Request $request, int $id): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $promo = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Promotion::findForBot($id, $b['id']);
            if ($found) { $promo = $found; $botId = $b['id']; break; }
        }
        if (!$promo) $this->abort(404);

        $this->render('admin/promotions/edit', [
            'title' => 'Sửa khuyến mãi',
            'promo' => $promo,
            'botId' => $botId,
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $promo = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Promotion::findForBot($id, $b['id']);
            if ($found) { $promo = $found; $botId = $b['id']; break; }
        }
        if (!$promo) $this->abort(404);

        Promotion::update($id, [
            'type'        => $request->string('type', 'percent'),
            'value'       => (float) $request->string('value'),
            'min_order'   => (float) $request->string('min_order', '0'),
            'max_uses'    => $request->int('max_uses', 0),
            'start_at'    => $request->string('start_at') ?: null,
            'end_at'      => $request->string('end_at') ?: null,
            'status' => $request->string('status', 'active'),
            'description' => $request->string('description'),
        ]);

        $this->redirectWithSuccess("/admin/promotions?bot_id=$botId", 'Cập nhật khuyến mãi thành công!');
    }

    public function destroy(Request $request, int $id): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = 0;
        foreach ($bots as $b) {
            if (Promotion::findForBot($id, $b['id'])) { $botId = $b['id']; Promotion::delete($id); break; }
        }
        $this->redirectWithSuccess("/admin/promotions?bot_id=$botId", 'Đã xoá khuyến mãi.');
    }
}
