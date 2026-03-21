<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Category;
use App\Models\Product;
use Core\Request;

class ProductController extends BaseController
{
    public function index(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $page  = $this->currentPage();
        $filters = [
            'search'      => $request->string('search'),
            'category_id' => $request->int('category_id'),
            'status'      => $request->string('status'),
        ];
        $products   = $botId ? Product::paginateForBot($botId, 20, $page, $filters) : ['data' => [], 'total' => 0, 'pages' => 0];
        $categories = $botId ? Category::forBot($botId) : [];

        $this->render('admin/products/index', [
            'title'      => 'Sản phẩm',
            'bots'       => $bots,
            'botId'      => $botId,
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $filters,
        ]);
    }

    public function create(Request $request): void
    {
        $user  = $this->authUser();
        $bots  = Bot::forAdmin($user['id']);
        $botId = $request->int('bot_id') ?: (int)($bots[0]['id'] ?? 0);
        $categories = $botId ? Category::forBot($botId) : [];

        $this->render('admin/products/create', [
            'title'      => 'Thêm sản phẩm',
            'bots'       => $bots,
            'botId'      => $botId,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): void
    {
        $user  = $this->authUser();
        $botId = $request->int('bot_id');
        $bot   = Bot::findForAdmin($botId, $user['id']);
        if (!$bot) { $this->backWithError('Bot không hợp lệ.'); return; }

        $name = trim($request->string('name'));
        if (!$name) { $this->backWithError('Tên sản phẩm không được trống.'); return; }

        Product::create([
            'bot_id'      => $botId,
            'category_id' => $request->int('category_id') ?: null,
            'name'        => $name,
            'slug'        => Product::generateSlug($name),
            'description' => $request->string('description'),
            'price'       => (float) $request->string('price'),
            'sale_price'  => $request->string('sale_price') ? (float) $request->string('sale_price') : null,
            'stock'       => $request->int('stock', -1),
            'sku'         => $request->string('sku'),
            'status' => $request->string('status', 'active'),
        ]);

        $this->redirectWithSuccess("/admin/products?bot_id=$botId", 'Thêm sản phẩm thành công!');
    }

    public function edit(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        $product = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Product::findForBot($id, $b['id']);
            if ($found) { $product = $found; $botId = $b['id']; break; }
        }
        if (!$product) $this->abort(404);

        $categories = Category::forBot($botId);
        $this->render('admin/products/edit', [
            'title'      => 'Sửa sản phẩm',
            'product'    => $product,
            'categories' => $categories,
            'botId'      => $botId,
        ]);
    }

    public function update(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        $product = null; $botId = 0;
        foreach ($bots as $b) {
            $found = Product::findForBot($id, $b['id']);
            if ($found) { $product = $found; $botId = $b['id']; break; }
        }
        if (!$product) $this->abort(404);

        $name = trim($request->string('name'));
        if (!$name) { $this->backWithError('Tên sản phẩm không được trống.'); return; }

        Product::update($id, [
            'category_id' => $request->int('category_id') ?: null,
            'name'        => $name,
            'description' => $request->string('description'),
            'price'       => (float) $request->string('price'),
            'sale_price'  => $request->string('sale_price') ? (float) $request->string('sale_price') : null,
            'stock'       => $request->int('stock', -1),
            'sku'         => $request->string('sku'),
            'status' => $request->string('status', 'active'),
        ]);

        $this->redirectWithSuccess("/admin/products?bot_id=$botId", 'Cập nhật sản phẩm thành công!');
    }

    public function destroy(Request $request, int $id): void
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        $botId = 0;
        foreach ($bots as $b) {
            if (Product::findForBot($id, $b['id'])) { $botId = $b['id']; Product::delete($id); break; }
        }
        $this->redirectWithSuccess("/admin/products?bot_id=$botId", 'Đã xoá sản phẩm.');
    }
}
