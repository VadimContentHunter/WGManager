<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Services/Autoloader.php';

use App\Services\ApiKeyService;
use App\Services\Autoloader;
use App\Services\ConsoleService;
use App\Services\SettingsService;

Autoloader::register();

$settings = new SettingsService();
$apiKeys = new ApiKeyService($settings);

$console = new ConsoleService();
$console
    ->command('apikey generate', function () use ($apiKeys, $console): void {

        if ($apiKeys->exists()) {
            $console->line('API-ключ уже существует.');

            return;
        }

        $console->line(
            'API Key: ' . $apiKeys->generate()
        );
    })

    ->command('apikey rotate', function () use ($apiKeys, $console): void {

        $console->line(
            'API Key: ' . $apiKeys->rotate()
        );
    })

    ->command('apikey clear', function () use ($apiKeys, $console): void {

        if (!$apiKeys->exists()) {
            $console->line('API-ключ отсутствует.');

            return;
        }

        $apiKeys->clear();

        $console->line(
            'API-ключ удалён.'
        );
    });

$console->run($argv);
