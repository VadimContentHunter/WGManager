<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\SettingsService;
use App\Services\WireGuardService;

class Client
{
    private SettingsService $settings;
    private WireGuardService $wireGuard;

    public function __construct()
    {
        $this->settings = new SettingsService();
        $this->wireGuard = new WireGuardService();
    }

    /**
     * Получить список клиентов.
     */
    public function all(): array
    {
        return $this->clients();
    }

    /**
     * Получить клиента.
     */
    public function show(string $id): ?array
    {
        foreach ($this->clients() as $client) {
            if ($client['id'] === $id) {
                return $client;
            }
        }

        return null;
    }

    /**
     * Создать клиента.
     */
    public function create(array $data): array
    {
        $client = [
            'id' => uniqid(),
            'name' => $data['name'] ?? 'Client',
            'ip' => $this->nextIp(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $clients = $this->clients();
        $clients[] = $client;

        return $client;
    }

    /**
     * Обновить клиента.
     */
    public function update(string $id, array $data): ?array
    {
        foreach ($this->clients() as $client) {

            if ($client['id'] !== $id) {
                continue;
            }

            return array_merge($client, $data);
        }

        return null;
    }

    /**
     * Удалить клиента.
     */
    public function delete(string $id): bool
    {
        foreach ($this->clients() as $client) {

            if ($client['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Скачать конфигурацию.
     */
    public function download(string $id): string
    {
        return "/configs/{$id}.conf";
    }

    /**
     * В будущем здесь будут данные SettingsService.
     */
    private function clients(): array
    {
        return [
            [
                'id' => '1',
                'name' => 'Office',
                'ip' => '10.0.0.2',
                'created_at' => '2026-07-23 18:00:00'
            ],
            [
                'id' => '2',
                'name' => 'Phone',
                'ip' => '10.0.0.3',
                'created_at' => '2026-07-23 18:10:00'
            ]
        ];
    }

    /**
     * Пока простая заглушка.
     */
    private function nextIp(): string
    {
        return '10.0.0.' . (count($this->clients()) + 2);
    }
}
