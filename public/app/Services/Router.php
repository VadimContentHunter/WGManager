<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Request;
use App\Services\Response;

/**
 * Класс Router
 * Обрабатывает маршрутизацию запросов в приложении.
 */
class Router
{
    /**
     * Маршруты, определенные в конфигурации.
     * Ключ - регулярное выражение, значение - массив обработчиков для каждого метода.
     *
     * @var array<string, array<string, array<class-string, string>>>
     */
    public function __construct(
        private array $routes,
    ) {}

    /**
     * Обрабатывает текущий HTTP-запрос, сопоставляя его с маршрутом и вызывая соответствующий обработчик.
     */
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

