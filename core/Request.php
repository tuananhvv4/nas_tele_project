<?php

declare(strict_types=1);

namespace Core;

class Request
{
    private array $params = [];

    public static function capture(): self
    {
        return new self();
    }

    // ── Method & Path ────────────────────────────────────────────────────────

    public function method(): string
    {
        // Support method override via _method field (e.g. PUT/DELETE from HTML forms)
        $override = strtoupper($_POST['_method'] ?? '');
        if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
            return $override;
        }
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . ltrim($path ?? '/', '/');
    }

    public function fullUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    // ── Input ────────────────────────────────────────────────────────────────

    public function input(string $key, mixed $default = null): mixed
    {
        $data = array_merge($_GET, $_POST);
        return $data[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    // ── Sanitisation ─────────────────────────────────────────────────────────

    public function string(string $key, string $default = ''): string
    {
        return trim(strip_tags((string)($this->all()[$key] ?? $default)));
    }

    public function int(string $key, int $default = 0): int
    {
        return (int)($this->all()[$key] ?? $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float)($this->all()[$key] ?? $default);
    }

    public function bool(string $key): bool
    {
        return filter_var($this->all()[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    // ── Headers & Meta ───────────────────────────────────────────────────────

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    // ── Route Params (set by Router) ─────────────────────────────────────────

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    // ── Body (JSON / raw) ─────────────────────────────────────────────────────

    public function body(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    public function json(): array
    {
        $decoded = json_decode($this->body(), true);
        return is_array($decoded) ? $decoded : [];
    }
}
