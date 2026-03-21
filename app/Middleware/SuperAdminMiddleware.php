<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware as MiddlewareInterface;
use Core\Request;
use Core\Response;
use Core\Session;

class SuperAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        if (!Session::getInstance()->isSuperAdmin()) {
            Response::abort(403, 'Bạn không có quyền truy cập khu vực này.');
        }
        $next();
    }
}
