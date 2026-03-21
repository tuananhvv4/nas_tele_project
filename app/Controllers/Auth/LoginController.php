<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\AdminUser;
use Core\Request;
use Core\Response;
use Core\Session;

class LoginController extends BaseController
{
    public function showLogin(Request $request): void
    {
        if (Session::getInstance()->isLoggedIn()) {
            Response::redirect('/admin/dashboard');
        }
        $this->render('auth/login', ['title' => 'Đăng nhập']);
    }

    public function login(Request $request): never
    {
        $username = $request->string('username');
        $password = $request->post('password', '');
        $token    = $request->post('_csrf_token', '');

        if (!Session::getInstance()->validateCsrf($token)) {
            Response::redirectWithError('/login', 'Phiên đã hết hạn. Vui lòng thử lại.');
        }

        if ($username === '' || $password === '') {
            Response::redirectWithError('/login', 'Vui lòng nhập tên đăng nhập và mật khẩu.');
        }

        $user = AdminUser::findByUsername($username);

        if (!$user || !AdminUser::verifyPassword($password, $user['password_hash'])) {
            Response::redirectWithError('/login', 'Tên đăng nhập hoặc mật khẩu không đúng.');
        }

        if (($user['status'] ?? 'active') !== 'active') {
            Response::redirectWithError('/login', 'Tài khoản đã bị vô hiệu hoá.');
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        Session::getInstance()->regenerateCsrf();
        Session::getInstance()->setAuth([
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);

        Response::redirect('/admin/dashboard');
    }

    public function logout(Request $request): never
    {
        Session::getInstance()->destroy();
        Response::redirect('/login');
    }
}
