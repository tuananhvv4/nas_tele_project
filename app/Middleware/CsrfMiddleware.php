<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Middleware as MiddlewareInterface;
use Core\Request;
use Core\Response;
use Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            $next();
            return;
        }

        $token = $request->post('_csrf_token')
            ?? $request->header('X-CSRF-Token')
            ?? '';

        if (!Session::getInstance()->validateCsrf($token)) {
            if ($request->isAjax()) {
                Response::jsonError('CSRF token mismatch.', 403);
            }
            Response::abort(403, 'CSRF token không hợp lệ. Vui lòng tải lại trang.');
        }

        $next();
    }
}
