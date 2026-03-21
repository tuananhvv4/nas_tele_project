<?php

declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AdminUser;
use Core\Request;
use Core\Response;

class FeatureController extends BaseController
{
    private const FEATURE_LABELS = [
        'products'    => 'Sản phẩm',
        'categories'  => 'Danh mục',
        'orders'      => 'Đơn hàng',
        'broadcast'   => 'Broadcast',
        'promotions'  => 'Khuyến mãi',
        'api_sources' => 'API Sources',
        'settings'    => 'Cài đặt',
        'bots'        => 'Quản lý Bots',
    ];

    public function edit(Request $request, int $id): void
    {
        $admin = AdminUser::find((int)$id);
        if (!$admin || $admin['role'] === 'super_admin') {
            $this->abort(404);
        }

        $perms = json_decode($admin['feature_permissions'] ?? '{}', true) ?? [];

        $this->render('super-admin/features/edit', [
            'title'         => 'Phân quyền tính năng — ' . htmlspecialchars($admin['username']),
            'layout'        => 'admin',
            'admin'         => $admin,
            'perms'         => $perms,
            'featureLabels' => self::FEATURE_LABELS,
        ]);
    }

    public function update(Request $request, int $id): never
    {
        $admin = AdminUser::find((int)$id);
        if (!$admin || $admin['role'] === 'super_admin') {
            $this->abort(404);
        }

        $perms = [];
        foreach (array_keys(self::FEATURE_LABELS) as $feature) {
            $perms[$feature] = $request->has("perm_{$feature}");
        }

        // Numeric limits
        $perms['max_products'] = max(0, $request->int('max_products', 100));
        $perms['max_bots']     = max(1, $request->int('max_bots', 1));

        AdminUser::updatePermissions((int)$id, $perms);
        Response::redirectWithSuccess("/super-admin/admins/{$id}/features", 'Đã cập nhật phân quyền.');
    }
}
