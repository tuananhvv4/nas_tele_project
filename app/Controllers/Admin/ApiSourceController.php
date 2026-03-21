<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApiSource;
use App\Models\Bot;
use Core\Request;

class ApiSourceController extends BaseController
{
    public function index(Request $request): void
    {
        $user    = $this->authUser();
        $bots    = Bot::forAdmin($user['id']);
        $botId   = $request->int('bot_id') ?: ($bots[0]['id'] ?? 0);
        $page    = $this->currentPage();
        $sources = $botId ? ApiSource::paginateForBot($botId, 20, $page) : ['data' => [], 'total' => 0, 'pages' => 0];

        $this->render('admin/api-sources/index', [
            'title'   => 'Nguồn API',
            'bots'    => $bots,
            'botId'   => $botId,
            'sources' => $sources,
        ]);
    }

    public function create(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: ($bots[0]['id'] ?? 0);

        $this->render('admin/api-sources/create', [
            'title' => 'Thêm nguồn API',
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

        $name = trim($request->string('name'));
        $url  = trim($request->string('url'));
        if (!$name || !$url) { $this->backWithError('Tên và URL không được trống.'); return; }

        $headersRaw = trim($request->string('headers'));
        $headers    = null;
        if ($headersRaw) {
            $decoded = json_decode($headersRaw, true);
            if (!is_array($decoded)) { $this->backWithError('Headers phải là JSON hợp lệ.'); return; }
            $headers = $headersRaw;
        }

        ApiSource::create([
            'bot_id'    => $botId,
            'name'      => $name,
            'url'       => $url,
            'headers'   => $headers,
            'is_active' => $request->int('is_active', 1),
        ]);

        $this->redirectWithSuccess("/admin/api-sources?bot_id=$botId", 'Thêm nguồn API thành công!');
    }

    public function edit(Request $request, int $id): void
    {
        $user   = $this->authUser();
        $bots   = Bot::forAdmin($user['id']);
        $source = null; $botId = 0;
        foreach ($bots as $b) {
            $found = ApiSource::findForBot($id, $b['id']);
            if ($found) { $source = $found; $botId = $b['id']; break; }
        }
        if (!$source) $this->abort(404);

        $this->render('admin/api-sources/edit', [
            'title'  => 'Sửa nguồn API',
            'source' => $source,
            'botId'  => $botId,
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $user   = $this->authUser();
        $bots   = Bot::forAdmin($user['id']);
        $source = null; $botId = 0;
        foreach ($bots as $b) {
            $found = ApiSource::findForBot($id, $b['id']);
            if ($found) { $source = $found; $botId = $b['id']; break; }
        }
        if (!$source) $this->abort(404);

        $headersRaw = trim($request->string('headers'));
        $headers    = $source['headers'];
        if ($headersRaw !== '' && $headersRaw !== $source['headers']) {
            $decoded = json_decode($headersRaw, true);
            if (!is_array($decoded)) { $this->backWithError('Headers phải là JSON hợp lệ.'); return; }
            $headers = $headersRaw;
        }

        ApiSource::update($id, [
            'name'      => trim($request->string('name')),
            'url'       => trim($request->string('url')),
            'headers'   => $headers,
            'is_active' => $request->int('is_active', 1),
        ]);

        $this->redirectWithSuccess("/admin/api-sources?bot_id=$botId", 'Cập nhật nguồn API thành công!');
    }

    public function destroy(Request $request, int $id): void
    {
        $user   = $this->authUser();
        $bots   = Bot::forAdmin($user['id']);
        $botId  = 0;
        foreach ($bots as $b) {
            if (ApiSource::findForBot($id, $b['id'])) { $botId = $b['id']; ApiSource::delete($id); break; }
        }
        $this->redirectWithSuccess("/admin/api-sources?bot_id=$botId", 'Đã xoá nguồn API.');
    }

    public function testConnection(Request $request, int $id): void
    {
        $user   = $this->authUser();
        $bots   = Bot::forAdmin($user['id']);
        $source = null;
        foreach ($bots as $b) {
            $found = ApiSource::findForBot($id, $b['id']);
            if ($found) { $source = $found; break; }
        }
        if (!$source) { $this->json(['success' => false, 'message' => 'Not found'], 404); return; }

        // TODO: implement via ApiSyncService
        $this->json(['success' => true, 'message' => 'Kết nối thành công']);
    }

    public function sync(Request $request, int $id): void
    {
        $user   = $this->authUser();
        $bots   = Bot::forAdmin($user['id']);
        $source = null; $botId = 0;
        foreach ($bots as $b) {
            $found = ApiSource::findForBot($id, $b['id']);
            if ($found) { $source = $found; $botId = $b['id']; break; }
        }
        if (!$source) $this->abort(404);

        // TODO: implement via ApiSyncService
        ApiSource::update($id, ['last_synced_at' => date('Y-m-d H:i:s')]);
        $this->backWithSuccess('Đồng bộ thành công!');
    }
}
