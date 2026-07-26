<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Response;
use App\Services\WireGuardSetupService;

class SetupController
{
    public function __construct(
        private readonly WireGuardSetupService $setup,
        private readonly Response $response,
    ) {}

    /**
     * Возвращает информацию о состоянии системы.
     */
    public function index(): void
    {
        $this->response->success(
            $this->setup->check()
        );
    }

    /**
     * Выполняет первоначальную настройку WireGuard.
     */
    public function initialize(): void
    {
        $this->setup->initialize();
        $this->response->success();
    }

    /**
     * Запускает интерфейс WireGuard.
     */
    public function start(): void
    {
        $this->setup->start();
        $this->response->success();
    }

    /**
     * Останавливает интерфейс WireGuard.
     */
    public function stop(): void
    {
        $this->setup->stop();
        $this->response->success();
    }

    /**
     * Перезапускает интерфейс WireGuard.
     */
    public function restart(): void
    {
        $this->setup->restart();
        $this->response->success();
    }
}
