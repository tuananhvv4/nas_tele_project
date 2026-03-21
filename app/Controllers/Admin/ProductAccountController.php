<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Bot;
use App\Models\Product;
use App\Models\ProductAccount;
use Core\Request;

class ProductAccountController extends BaseController
{
    /** Verify product belongs to current admin, return product or abort */
    private function findProduct(int $productId): array
    {
        $user = $this->authUser();
        $bots = Bot::forAdmin($user['id']);
        foreach ($bots as $bot) {
            $product = Product::findForBot($productId, (int)$bot['id']);
            if ($product) return $product;
        }
        $this->abort(404);
    }

    public function index(Request $request, int $productId): void
    {
        $product  = $this->findProduct($productId);
        $page     = $this->currentPage();
        $accounts = ProductAccount::forProduct($productId, 50, $page);
        $counts   = ProductAccount::countByStatus($productId);

        $this->render('admin/products/accounts/index', [
            'title'     => 'Tài khoản: ' . $product['name'],
            'product'   => $product,
            'accounts'  => $accounts,
            'counts'    => $counts,
        ]);
    }

    public function store(Request $request, int $productId): void
    {
        $this->findProduct($productId);

        $raw = $request->input('values', '');
        if (empty(trim((string)$raw))) {
            $this->backWithError('Vui lòng nhập ít nhất một tài khoản.');
            return;
        }

        $lines = explode("\n", str_replace("\r\n", "\n", (string)$raw));
        $count = ProductAccount::bulkCreate($productId, $lines);

        $this->redirectWithSuccess(
            "/admin/products/$productId/accounts",
            "Đã thêm $count tài khoản thành công!"
        );
    }

    public function destroy(Request $request, int $productId, int $accountId): void
    {
        $this->findProduct($productId);

        $account = ProductAccount::find($accountId);
        if (!$account || (int)$account['product_id'] !== $productId) {
            $this->abort(404);
        }
        if ($account['status'] === 'used') {
            $this->backWithError('Không thể xoá tài khoản đã được sử dụng.');
            return;
        }

        ProductAccount::delete($accountId);
        $this->redirectWithSuccess(
            "/admin/products/$productId/accounts",
            'Đã xoá tài khoản.'
        );
    }

    public function reset(Request $request, int $productId): void
    {
        $this->findProduct($productId);

        $deleted = ProductAccount::deleteAvailable($productId);
        $this->redirectWithSuccess(
            "/admin/products/$productId/accounts",
            "Đã xoá $deleted tài khoản chưa sử dụng."
        );
    }
}
