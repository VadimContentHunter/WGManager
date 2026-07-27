<?php

declare(strict_types=1);

namespace App\Services;

use App\Controllers\ApiKeyController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\SettingsController;
use App\Controllers\SetupController;
use App\Controllers\WebController;
use RuntimeException;

/**
 * Класс Router.
 *
 * Обрабатывает маршрутизацию HTTP-запросов.
 */
class Router
{
    /**
     * @param array<string, array<string, array<class-string, string>>> $routes
     */
    public function __construct(
        private readonly array $routes,
    ) {}

    /**
     * Обрабатывает текущий HTTP-запрос.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        $uri = trim(
            parse_url(
                $_SERVER['REQUEST_URI'],
                PHP_URL_PATH
            ),
            '/'
        );

        foreach ($this->routes as $pattern => $handlers) {

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            if (!isset($handlers[$method])) {

                (new Response())->error(
                    'Method Not Allowed',
                    405
                );

                return;
            }

            $route = [];

            foreach ($matches as $key => $value) {

                if (!is_int($key)) {
                    $route[$key] = urldecode($value);
                }
            }

            $request = new Request($route);
            $response = new Response();

            $settings = new SettingsService();
            $apiKeys = new ApiKeyService($settings);

            if (
                !$this->authorize(
                    $uri,
                    $method,
                    $request,
                    $response,
                    $apiKeys
                )
            ) {
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
                        new WireGuardService($settings, new CommandService())
                    )
                ),

                ApiKeyController::class => new ApiKeyController(
                    $request,
                    $response,
                    $apiKeys
                ),

                SettingsController::class => new SettingsController(
                    $request,
                    $response,
                    $settings
                ),

                AuthController::class => new AuthController(
                    $response
                ),

                SetupController::class => new SetupController(
                    new WireGuardSetupService(
                        $settings,
                        new CommandService(),
                        new WireGuardService(
                            $settings,
                            new CommandService()
                        )
                    ),
                    $response
                ),

                default => throw new RuntimeException(
                    "Неизвестный контроллер: {$controller}"
                ),
            };

            $controller->$action();

            return;
        }

        (new Response())->notFound();
    }

    /**
     * Проверяет авторизацию API.
     */
    private function authorize(
        string $uri,
        string $method,
        Request $request,
        Response $response,
        ApiKeyService $apiKeys
    ): bool {

        if (
            !$this->requiresAuthorization(
                $uri,
                $method
            )
        ) {
            return true;
        }

        if (!$apiKeys->exists()) {
            return true;
        }

        if (
            $apiKeys->validate(
                $request->header('X-API-Key')
            )
        ) {
            return true;
        }

        $response->unauthorized();

        return false;
    }

    /**
     * Определяет, требуется ли авторизация.
     */
    private function requiresAuthorization(
        string $uri,
        string $method
    ): bool {

        if (
            $uri === 'api/api-key'
            && $method === 'POST'
        ) {
            return false;
        }

        return str_starts_with(
            $uri,
            'api/'
        );
    }
}
