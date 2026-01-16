<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function dispatch(string $method, string $path): void
    {
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->matchRoute($route['regex'], $route['parameters'], $path);
            if ($params === null) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                if (!$middleware->handle()) {
                    return;
                }
            }

            call_user_func_array($route['handler'], $params);
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function addRoute(string $method, string $path, callable $handler, array $middleware): void
    {
        [$regex, $parameters] = $this->compileRoute($path);

        $this->routes[$method][] = [
            'path' => $path,
            'regex' => $regex,
            'parameters' => $parameters,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function compileRoute(string $path): array
    {
        $parameters = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $matches) use (&$parameters): string {
            $parameters[] = $matches[1];
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);

        return ['#^' . $regex . '$#', $parameters];
    }

    private function matchRoute(string $regex, array $parameters, string $path): ?array
    {
        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $values = [];
        foreach ($parameters as $parameter) {
            $values[] = $matches[$parameter];
        }

        return $values;
    }
}
