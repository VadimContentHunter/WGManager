<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Services/Autoloader.php';

use App\Services\Autoloader;
use App\Services\Request;
use App\Services\Response;
use App\Services\Router;

Autoloader::register();

$router = new Router(
    require __DIR__ . '/config/routes.php' ?? [],
    new Request(),
    new Response()
);

$router->dispatch();
