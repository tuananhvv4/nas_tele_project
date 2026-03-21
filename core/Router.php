<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    private array $middlewareGroups = [];
    private array $currentGroupMiddleware = [];
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // ── Route registration ───────────────────────────────────────────────────

    public function get(string $uri, array|callable|string $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, array|callable|string $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, array|callable|string $action, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, array|callable|string $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previous = $this->currentGroupMiddleware;
        $this->currentGroupMiddleware = array_merge(
            $previous,
            $attributes['middleware'] ?? []
        );
        $callback($this);
        $this->currentGroupMiddleware = $previous;
    }

    private function addRoute(string $method, string $uri, array|callable|string $action, array $middleware): self
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => rtrim($uri, '/') ?: '/',
            'action'     => $action,
            'middleware' => array_merge($this->currentGroupMiddleware, $middleware),
        ];
        return $this;
    }

    // ── Dispatching ──────────────────────────────────────────────────────────

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path   = rtrim($request->path(), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchUri($route['uri'], $path);
            if ($params === false) {
                continue;
            }

            $this->runMiddlewareChain($route['middleware'], $request, function () use ($route, $request, $params) {
                $this->callAction($route['action'], $params, $request);
            });
            return;
        }

        // 404 fallback
        http_response_code(404);
        $view = new View($this->app->basePath('resources/views'));
        if (file_exists($this->app->basePath('resources/views/errors/404.php'))) {
            echo $view->render('errors/404');
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }

    private function matchUri(string $routeUri, string $requestPath): array|false
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $routeUri);
        $pattern = '@^' . $pattern . '$@';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        // Extract param names
        preg_match_all('/\{([a-zA-Z_]+)\}/', $routeUri, $paramNames);
        array_shift($matches); // remove full match

        return array_combine($paramNames[1], $matches) ?: [];
    }

    private function runMiddlewareChain(array $middleware, Request $request, callable $final): void
    {
        $chain = array_reduce(
            array_reverse($middleware),
            function ($carry, $mw) use ($request) {
                return function () use ($carry, $mw, $request) {
                    $instance = $this->resolveMiddleware($mw);
                    $instance->handle($request, $carry);
                };
            },
            $final
        );
        $chain();
    }

    private function resolveMiddleware(string $alias): object
    {
        $map = [
            'auth'        => \App\Middleware\AuthMiddleware::class,
            'csrf'        => \App\Middleware\CsrfMiddleware::class,
            'super_admin' => \App\Middleware\SuperAdminMiddleware::class,
        ];

        // Support 'permission:feature_name'
        if (str_starts_with($alias, 'permission:')) {
            $feature = substr($alias, 11);
            return new \App\Middleware\PermissionMiddleware($feature);
        }

        $class = $map[$alias] ?? $alias;
        return new $class();
    }

    private function callAction(array|callable|string $action, array $params, Request $request): void
    {
        if (is_callable($action)) {
            $result = $action($request, ...$params);
            if (is_string($result)) echo $result;
            return;
        }

        if (is_string($action)) {
            $action = explode('@', $action);
        }

        [$controllerClass, $method] = $action;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller [{$controllerClass}] not found.");
        }

        $controller = new $controllerClass();
        // Cast numeric URL params (always strings from regex) to int
        $params = array_map(fn($v) => is_numeric($v) ? (int)$v : $v, $params);
        $controller->$method($request, ...$params);
    }
}
