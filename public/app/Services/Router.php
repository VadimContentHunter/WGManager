<?php

declare(strict_types=1);

namespace App\Services;

use App\Controllers\ApiKeyController;
use App\Controllers\ClientController;
use App\Controllers\WebController;
use RuntimeException;

/**
 * Класс Router
 * Обрабатывает маршрутизацию запросов в приложении.
 */
class Router
{
    /**
     * @param array<string, array<string, array<class-string, string>>> $routes
     */
    public function __construct(
        private array $routes,
    ) {}

    /**
     * Обрабатывает текущий HTTP-запрос.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );

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
            $apiKeys = new ApiKeyService($settings);

            if (
                str_starts_with($uri, 'api/')
                && $apiKeys->exists()
                && !$apiKeys->validate(
                    $request->header('X-API-Key')
                )
            ) {
                $response->unauthorized();

                return;
            }

            [$controller, $action] = $handlers[$method];
            $controller = match ($controller) {
                WebController::class => new WebController(
                    $response
                ),

                ClientController::class => new ClientController(
                    $request,
                    $response,
                    new ClientService(
                        new WireGuardService($settings)
                    )
                ),

                ApiKeyController::class => new ApiKeyController(
                    $request,
                    $response,
                    $apiKeys
                ),

                default => throw new RuntimeException(
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
