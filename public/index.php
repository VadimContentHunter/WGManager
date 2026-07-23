<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Services/Autoloader.php';

use App\Services\Autoloader;
use App\Services\Response;
use App\Services\Router;

Autoloader::register();

$response = new Response();

try {

    $router = new Router(
        require __DIR__ . '/config/routes.php'
    );

    $router->dispatch();
} catch (InvalidArgumentException $e) {

    $response->error(
        $e->getMessage(),
        400
    );
} catch (RuntimeException $e) {

    $response->error(
        $e->getMessage(),
        400
    );
} catch (Throwable $e) {

    $response->internalError(
        $e->getMessage()
    );
}
