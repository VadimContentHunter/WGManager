<?php

declare(strict_types=1);

use App\Controllers\ApiKeyController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\SettingsController;
use App\Controllers\SetupController;
use App\Controllers\WebController;

return [

    '#^$#' => [
        'GET' => [
            WebController::class,
            'dashboard',
        ],
    ],

    '#^api/clients$#' => [
        'GET' => [ClientController::class, 'list'],
        'POST' => [ClientController::class, 'create'],
    ],

    '#^api/clients/(?<publicKey>[^/]+)$#' => [
        'GET'    => [ClientController::class, 'show'],
        'PUT'    => [ClientController::class, 'update'],
        'DELETE' => [ClientController::class, 'delete'],
    ],

    '#^api/clients/(?<publicKey>[^/]+)/config$#' => [
        'GET' => [ClientController::class, 'download'],
    ],

    '#^api/api-key$#' => [
        'GET'  => [ApiKeyController::class, 'show'],
        'POST' => [ApiKeyController::class, 'create'],
        'PUT'  => [ApiKeyController::class, 'rotate'],
    ],

    '#^api/auth/check$#' => [
        'GET' => [
            AuthController::class,
            'check',
        ],
    ],

    '#^api/settings$#' => [
        'GET' => [SettingsController::class, 'show'],
        'PUT' => [SettingsController::class, 'update'],
    ],

    '#^api/setup$#' => [
        'GET' => [SetupController::class, 'index'],
    ],

    '#^api/setup/install$#' => [
        'POST' => [SetupController::class, 'install'],
    ],

    '#^api/setup/update$#' => [
        'POST' => [SetupController::class, 'update'],
    ],

    '#^api/setup/initialize$#' => [
        'POST' => [SetupController::class, 'initialize'],
    ],

    '#^api/setup/start$#' => [
        'POST' => [SetupController::class, 'start'],
    ],

    '#^api/setup/stop$#' => [
        'POST' => [SetupController::class, 'stop'],
    ],

    '#^api/setup/restart$#' => [
        'POST' => [SetupController::class, 'restart'],
    ],

];
