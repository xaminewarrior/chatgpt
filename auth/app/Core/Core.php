<?php

declare(strict_types=1);

namespace App\Core;

final class Core
{
    private string $currentController = 'Auth';
    private string $currentMethod = 'showLogin';
    private array $params = [];

    public function __construct(private Container $container)
    {
        $url = $this->getUrl();

        if (!empty($url[0])) {
            $controllerName = $this->formatControllerName($url[0]);
            if (class_exists($controllerName)) {
                $this->currentController = $url[0];
                unset($url[0]);
            }
        }

        $controllerClass = $this->formatControllerName($this->currentController);
        $controller = $this->container->get($controllerClass);

        if (isset($url[1])) {
            $method = $url[1];
            if (method_exists($controller, $method)) {
                $this->currentMethod = $method;
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : [];

        if (method_exists($controller, 'middlewareFor')) {
            $middleware = $controller->middlewareFor($this->currentMethod);
            foreach ($middleware as $guard) {
                if (!$guard->handle()) {
                    return;
                }
            }
        }

        call_user_func_array([$controller, $this->currentMethod], $this->params);
    }

    private function formatControllerName(string $segment): string
    {
        $controller = ucfirst($segment) . 'Controller';

        return 'App\\Controllers\\' . $controller;
    }

    private function getUrl(): array
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return $url === '' ? [] : explode('/', $url);
        }

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $path = trim($path, '/');

        return $path === '' ? [] : explode('/', $path);
    }
}
