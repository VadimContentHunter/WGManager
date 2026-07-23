<?php

declare(strict_types=1);

use App\Controllers\ApiKeyController;
use App\Controllers\ClientController;
use App\Controllers\SettingsController;
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

    '#^api/settings$#' => [
        'GET' => [SettingsController::class, 'show'],
        'PUT' => [SettingsController::class, 'update'],
    ],

];