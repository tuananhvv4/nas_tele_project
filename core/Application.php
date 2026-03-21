<?php

declare(strict_types=1);

namespace Core;

use Dotenv\Dotenv;

class Application
{
    private static self $instance;
    private array $bindings = [];
    private string $basePath;

    private function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public static function getInstance(string $basePath = ''): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }

    public function bootstrap(): void
    {
        // Load .env
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();

        // Timezone
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

        // Error reporting
        if ($_ENV['APP_DEBUG'] === 'true') {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            error_reporting(0);
        }

        // Register core bindings
        $this->bind('db', fn() => Database::getInstance());
        $this->bind('session', fn() => Session::getInstance());
        $this->bind('request', fn() => Request::capture());
        $this->bind('view', fn() => new View($this->basePath . '/resources/views'));
    }

    public function bind(string $key, callable $resolver): void
    {
        $this->bindings[$key] = $resolver;
    }

    public function make(string $key): mixed
    {
        if (isset($this->bindings[$key])) {
            return ($this->bindings[$key])();
        }
        throw new \RuntimeException("No binding found for [{$key}]");
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function run(): void
    {
        Session::getInstance()->start();
        $router = new Router($this);
        require $this->basePath . '/routes/web.php';
        $router->dispatch(Request::capture());
    }

    /** Minimal bootstrap for CLI/webhook use — no session or router */
    public function bootstrapLite(): void
    {
        $this->bootstrap();
        $this->bind('db', fn() => Database::getInstance());
    }
}
