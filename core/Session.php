<?php

declare(strict_types=1);

namespace Core;

class Session
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $name     = $_ENV['SESSION_NAME']    ?? 'bot_admin_session';
            $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);
            $secure   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            session_name($name);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ── Get / Set / Forget ────────────────────────────────────────────────────

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function all(): array
    {
        return $_SESSION ?? [];
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'],
                $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    // ── Flash Messages ────────────────────────────────────────────────────────

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // ── Old Input (for form repopulation after validation errors) ─────────────

    public function flashOldInput(array $data): void
    {
        $_SESSION['_old_input'] = $data;
    }

    public function getOldInput(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    public function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }

    // ── Auth Helpers ──────────────────────────────────────────────────────────

    public function setAuth(array $user): void
    {
        $_SESSION['auth_user'] = $user;
    }

    public function getAuth(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['auth_user']);
    }

    public function isSuperAdmin(): bool
    {
        return ($this->getAuth()['role'] ?? '') === 'super_admin';
    }

    public function clearAuth(): void
    {
        unset($_SESSION['auth_user']);
    }

    // ── CSRF ──────────────────────────────────────────────────────────────────

    public function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public function validateCsrf(string $token): bool
    {
        $expected = $_SESSION['_csrf_token'] ?? '';
        return hash_equals($expected, $token);
    }

    public function regenerateCsrf(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}
