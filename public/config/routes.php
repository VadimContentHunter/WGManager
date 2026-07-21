<?php

use App\Controllers\ClientController;

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
