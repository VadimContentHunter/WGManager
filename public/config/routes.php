<?php

use App\Controllers\ClientController;

/**
 * Маршруты приложения.
 * Ключ - регулярное выражение, значение - массив обработчиков для каждого метода.
 *
 * @var array<string, array<string, array<class-string, string>>>
 */
return [

    '#^api/clients$#' => [
        'GET' => [
            ClientController::class,
            'list',
        ],
    ],

    '#^api/client$#' => [
        'POST' => [
            ClientController::class,
            'create',
        ],
    ],

    '#^api/client/(?<id>\d+)$#' => [

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

];

