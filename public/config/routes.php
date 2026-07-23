<?php

declare(strict_types=1);

use App\Controllers\ApiKeyController;
use App\Controllers\ClientController;

/**
 * Маршруты приложения.
 *
 * @var array<string, array<string, array{0: class-string, 1: string}>>
 */
return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    */

    '#^api/apikey$#' => [

        'GET' => [
            ApiKeyController::class,
            'show',
        ],

        'POST' => [
            ApiKeyController::class,
            'create',
        ],

        'PUT' => [
            ApiKeyController::class,
            'rotate',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Клиенты
    |--------------------------------------------------------------------------
    */

    '#^api/clients$#' => [

        'GET' => [
            ClientController::class,
            'list',
        ],

        'POST' => [
            ClientController::class,
            'create',
        ],
    ],

    '#^api/clients/(?<publicKey>[^/]+)$#' => [

        'GET' => [
            ClientController::class,
            'show',
        ],

        'PATCH' => [
            ClientController::class,
            'update',
        ],

        'DELETE' => [
            ClientController::class,
            'delete',
        ],
    ],

    '#^api/clients/(?<publicKey>[^/]+)/config$#' => [

        'GET' => [
            ClientController::class,
            'download',
        ],
    ],

];
