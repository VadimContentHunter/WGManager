<?php

declare(strict_types=1);

namespace App\Services;

class Router
{
    public function __construct(
        private array $routes,
    ) {}

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        foreach ($this->routes as $pattern => $handlers) {
            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            if (!isset($handlers[$method])) {
                (new Response())->json([
                    'status'  => false,
                    'message' => 'Method Not Allowed',
                ], 405);

                return;
            }

            $route = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $route[$key] = $value;
                }
            }

            $request = new Request($route);
            $response = new Response();
            [$controller, $action] = $handlers[$method];
            $controller = new $controller(
                $request,
                $response
            );

            $controller->$action();
            return;
        }

        (new Response())->json([
            'status'  => false,
            'message' => 'Route Not Found',
        ], 404);
    }
}
