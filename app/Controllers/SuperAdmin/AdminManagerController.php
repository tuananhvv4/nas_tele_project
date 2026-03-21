<?php

declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AdminUser;
use Core\Request;
use Core\Response;

class AdminManagerController extends BaseController
{
    public function index(Request $request): void
    {
        $admins = AdminUser::allAdmins();
        $this->render('super-admin/admins/index', [
            'title'   => 'Quản lý Admins',
            'layout'  => 'admin',
            'admins'  => $admins,
        ]);
    }

    public function create(Request $request): void
    {
        $this->render('super-admin/admins/create', [
            'title'  => 'Tạo Admin mới',
            'layout' => 'admin',
        ]);
    }

    public function store(Request $request): never
    {
        $username    = $request->string('username');
        $password    = $request->post('password', '');
        $displayName = $request->string('display_name');
        $email       = $request->string('email');
        $maxBots     = $request->int('max_bots', 1);

        if (strlen($username) < 3) {
            Response::backWithError('Tên đăng nhập phải có ít nhất 3 ký tự.');
        }

        if (strlen($password) < 8) {
            Response::backWithError('Mật khẩu phải có ít nhất 8 ký tự.');
        }

        if (AdminUser::findByUsername($username)) {
            Response::backWithError('Tên đăng nhập đã tồn tại.');
        }

        $authUser = $this->authUser();
        AdminUser::create([
            'username'            => $username,
            'password_hash'       => AdminUser::hashPassword($password),
            'display_name'        => $displayName ?: $username,
            'email'               => $email ?: null,
            'role'                => 'admin',
            'feature_permissions' => json_encode(AdminUser::defaultPermissions()),
            'max_bots'            => max(1, $maxBots),
            'status'              => 'active',
            'created_by'          => $authUser['id'],
        ]);

        Response::redirectWithSuccess('/super-admin/admins', "Đã tạo admin: {$username}");
    }

    public function edit(Request $request, int $id): void
    {
        $admin = AdminUser::find((int)$id);
        if (!$admin || $admin['role'] === 'super_admin') {
            $this->abort(404);
        }

        $this->render('super-admin/admins/edit', [
            'title'  => 'Chỉnh sửa Admin',
            'layout' => 'admin',
            'admin'  => $admin,
        ]);
    }

    public function update(Request $request, int $id): never
    {
        $admin = AdminUser::find((int)$id);
        if (!$admin || $admin['role'] === 'super_admin') {
            $this->abort(404);
        }

        $data = [
            'display_name' => $request->string('display_name'),
            'email'        => $request->string('email') ?: null,
            'max_bots'     => max(1, $request->int('max_bots', 1)),
            'status'       => in_array($request->string('status'), ['active', 'inactive'], true)
                              ? $request->string('status') : 'active',
        ];

        // Update password only if provided
        $newPassword = $request->post('password', '');
        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                Response::backWithError('Mật khẩu mới phải có ít nhất 8 ký tự.');
            }
            $data['password_hash'] = AdminUser::hashPassword($newPassword);
        }

        AdminUser::update((int)$id, $data);
        Response::redirectWithSuccess('/super-admin/admins', 'Đã cập nhật thông tin admin.');
    }

    public function destroy(Request $request, int $id): never
    {
        $admin = AdminUser::find((int)$id);
        if (!$admin || $admin['role'] === 'super_admin') {
            $this->abort(404);
        }

        AdminUser::delete((int)$id);
        Response::redirectWithSuccess('/super-admin/admins', 'Đã xoá admin.');
    }
}
