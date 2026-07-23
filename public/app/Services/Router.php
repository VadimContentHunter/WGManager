<?php

declare(strict_types=1);

namespace App\Services;

use App\Controllers\ApiKeyController;
use App\Controllers\ClientController;
use App\Services\ApiKeyService;
use App\Services\ClientService;
use App\Services\Request;
use App\Services\Response;
use App\Services\SettingsService;
use App\Services\WireGuardService;

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

            $settings = new SettingsService();
            $wireGuard = new WireGuardService($settings);
            $clientService = new ClientService($wireGuard);
            $apiKeys = new ApiKeyService($settings);

            if (
                $apiKeys->exists()
                && !$apiKeys->validate(
                    $request->header('X-API-Key')
                )
            ) {
                $response->unauthorized();

                return;
            }

            [$controller, $action] = $handlers[$method];

            $controller = match ($controller) {
                ClientController::class => new $controller(
                    $request,
                    $response,
                    $clientService
                ),

                ApiKeyController::class => new $controller(
                    $request,
                    $response,
                    $apiKeys
                ),

                default => throw new \RuntimeException(
                    "Неизвестный контроллер: {$controller}"
                ),
            };

            $controller->$action();
            return;
        }

        (new Response())->json([
            'status'  => false,
            'message' => 'Route Not Found',
        ], 404);
    }
}
