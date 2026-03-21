<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware as MiddlewareInterface;
use Core\Request;
use Core\Response;
use Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): void
    {
        if (!Session::getInstance()->isLoggedIn()) {
            Session::getInstance()->flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            Response::redirect('/login');
        }
        $next();
    }
}
