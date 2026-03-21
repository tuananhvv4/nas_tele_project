<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Category;
use Core\Request;

class CategoryController extends BaseController
{
    private function getBot(Request $request): ?array
    {
        $botId = $request->int('bot_id') ?: (int) ($_SESSION['current_bot_id'] ?? 0);
        if (!$botId) return null;
        return Bot::findForAdmin($botId, $this->authUser()['id']);
    }

    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $categories = $botId ? Category::forBot($botId) : [];

        $this->render('admin/categories/index', [
            'title'      => 'Danh mục',
            'bots'       => $bots,
            'botId'      => $botId,
            'categories' => $categories,
        ]);
    }

    public function create(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $parents = $botId ? Category::forBot($botId) : [];

        $this->render('admin/categories/create', [
            'title'   => 'Thêm danh mục',
            'bots'    => $bots,
            'botId'   => $botId,
            'parents' => $parents,
        ]);
    }

    public function store(Request $request): void
    {
        $user  = $this->authUser();
        $botId = $request->int('bot_id');
        $bot   = Bot::findForAdmin($botId, $user['id']);
        if (!$bot) { $this->backWithError('Bot không hợp lệ.'); return; }

        $name = trim($request->string('name'));
        if (!$name) { $this->backWithError('Tên danh mục không được trống.'); return; }

        Category::create([
            'bot_id'      => $botId,
            'parent_id'   => $request->int('parent_id') ?: null,
            'name'        => $name,
            'slug'        => Category::generateSlug($name),
            'description' => $request->string('description'),
            'sort_order'  => $request->int('sort_order', 0),
            'status' => $request->string('status', 'active'),
        ]);

        $this->redirectWithSuccess("/admin/categories?bot_id=$botId", 'Thêm danh mục thành công!');
    }

    public function edit(Request $request, int $id): void
    {
        $user     = $this->authUser();
        $category = null;
        $botId    = 0;
        // Find which bot owns this category
        $bots = Bot::forAdmin($user['id']);
        foreach ($bots as $b) {
            $found = Category::findForBot($id, $b['id']);
            if ($found) { $category = $found; $botId = $b['id']; break; }
        }
        if (!$category) $this->abort(404);

        $parents = Category::forBot($botId);
        $this->render('admin/categories/edit', [
            'title'    => 'Sửa danh mục',
            'category' => $category,
            'parents'  => $parents,
            'botId'    => $botId,
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        $category = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Category::findForBot($id, $b['id']);
            if ($found) { $category = $found; $botId = $b['id']; break; }
        }
        if (!$category) $this->abort(404);

        $name = trim($request->string('name'));
        if (!$name) { $this->backWithError('Tên danh mục không được trống.'); return; }

        Category::update($id, [
            'parent_id'   => $request->int('parent_id') ?: null,
            'name'        => $name,
            'description' => $request->string('description'),
            'sort_order'  => $request->int('sort_order', 0),
            'status' => $request->string('status', 'active'),
        ]);

        $this->redirectWithSuccess("/admin/categories?bot_id=$botId", 'Cập nhật danh mục thành công!');
    }

    public function destroy(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        foreach ($bots as $b) {
            if (Category::findForBot($id, $b['id'])) { Category::delete($id); break; }
        }
        $botId = $request->int('bot_id');
        $this->redirectWithSuccess("/admin/categories?bot_id=$botId", 'Đã xoá danh mục.');
    }
}
