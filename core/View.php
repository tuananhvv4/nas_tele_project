<?php

declare(strict_types=1);

namespace Core;

class View
{
    private string $viewPath;
    private static ?self $instance = null;

    public function __construct(string $viewPath)
    {
        $this->viewPath = rtrim($viewPath, '/');
    }

    public static function init(string $viewPath): void
    {
        self::$instance = new self($viewPath);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('View not initialised. Call View::init() first.');
        }
        return self::$instance;
    }

    /**
     * Render a view file with data.
     * If the view sets $layout, it wraps the content in the layout.
     */
    public function render(string $template, array $data = []): string
    {
        $file = $this->viewPath . '/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View [{$template}] not found at [{$file}].");
        }

        // Extract data as variables
        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        $content = ob_get_clean();

        // If the view defined $layout, wrap content in the layout
        if (isset($layout)) {
            $layoutFile = $this->viewPath . '/layouts/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout [{$layout}] not found at [{$layoutFile}].");
            }
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }

        return $content;
    }

    // ── Template helpers ──────────────────────────────────────────────────────

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function old(string $key, string $default = ''): string
    {
        $old = Session::getInstance()->get('_old_input', []);
        return self::e($old[$key] ?? $default);
    }

    public static function csrfField(): string
    {
        $token = Session::getInstance()->csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::e($token) . '">';
    }

    public static function methodField(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }

    public static function asset(string $path): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/assets/' . ltrim($path, '/');
    }

    public static function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
