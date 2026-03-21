<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\AdminUser;
use Core\Middleware as MiddlewareInterface;
use Core\Request;
use Core\Response;
use Core\Session;

class PermissionMiddleware implements MiddlewareInterface
{
    private string $feature;

    public function __construct(string $feature)
    {
        $this->feature = $feature;
    }

    public function handle(Request $request, callable $next): void
    {
        $auth = Session::getInstance()->getAuth();

        if (!$auth) {
            Response::redirect('/login');
        }

        // Super admin bypasses all permission checks
        if ($auth['role'] === AdminUser::ROLE_SUPER_ADMIN) {
            $next();
            return;
        }

        if (!AdminUser::can((int)$auth['id'], $this->feature)) {
            Response::abort(403, 'Tính năng này chưa được kích hoạt cho tài khoản của bạn.');
        }

        $next();
    }
}
