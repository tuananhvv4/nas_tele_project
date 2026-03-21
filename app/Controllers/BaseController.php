<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\View;
use Core\Response;
use Core\Session;

abstract class BaseController
{
    protected View $view;
    protected Session $session;

    public function __construct()
    {
        $this->view    = View::getInstance();
        $this->session = Session::getInstance();
    }

    protected function render(string $template, array $data = []): void
    {
        // Inject auth user and flash messages into every view
        $data['authUser']     = $this->session->getAuth();
        $data['flashSuccess'] = $this->session->getFlash('success');
        $data['flashError']   = $this->session->getFlash('error');
        $data['flashInfo']    = $this->session->getFlash('info');

        echo $this->view->render($template, $data);
    }

    protected function redirect(string $url): never
    {
        Response::redirect($url);
    }

    protected function redirectWithSuccess(string $url, string $message): never
    {
        Response::redirectWithSuccess($url, $message);
    }

    protected function redirectWithError(string $url, string $message): never
    {
        Response::redirectWithError($url, $message);
    }

    protected function back(): never
    {
        Response::back();
    }

    protected function backWithSuccess(string $message): never
    {
        Response::backWithSuccess($message);
    }

    protected function backWithError(string $message): never
    {
        Response::backWithError($message);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function abort(int $code, string $message = ''): never
    {
        Response::abort($code, $message);
    }

    protected function currentPage(): int
    {
        return max(1, (int)($_GET['page'] ?? 1));
    }

    protected function authUser(): ?array
    {
        $user = $this->session->getAuth();
        if ($user !== null && isset($user['id'])) {
            $user['id'] = (int)$user['id'];
        }
        return $user;
    }

    protected function isSuperAdmin(): bool
    {
        return $this->session->isSuperAdmin();
    }
}
