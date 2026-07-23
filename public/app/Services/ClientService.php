<?php

declare(strict_types=1);

namespace App\Services;

class ClientService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly WireGuardService $wireGuard,
    ) {}

    /**
     * Получить список клиентов.
     */
    public function all(): array
    {
        return $this->wireGuard->peers();
    }

    /**
     * Получить клиента по ID.
     */
    public function show(string $id): ?array
    {
        foreach ($this->all() as $client) {
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
        $this->validate($data);

        if ($this->exists($data['name'])) {
            throw new \RuntimeException('Клиент уже существует.');
        }

        $client = [
            'id'         => uniqid(),
            'name'       => trim($data['name']),
            'privateKey' => $this->generatePrivateKey(),
            'publicKey'  => $this->generatePublicKey(),
            'ip'         => $this->getFreeIp(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->wireGuard->addPeer($client);
        $this->wireGuard->save();
        $this->wireGuard->apply();

        return $client;
    }

    /**
     * Обновить клиента.
     */
    public function update(string $id, array $data): ?array
    {
        $client = $this->show($id);

        if ($client === null) {
            return null;
        }

        $client = array_merge($client, $data);

        $this->wireGuard->updatePeer(
            $client['publicKey'],
            $client
        );

        $this->wireGuard->save();
        $this->wireGuard->apply();

        return $client;
    }

    /**
     * Удалить клиента.
     */
    public function delete(string $id): bool
    {
        $client = $this->show($id);

        if ($client === null) {
            return false;
        }

        $this->wireGuard->removePeer(
            $client['publicKey']
        );

        $this->wireGuard->save();
        $this->wireGuard->apply();

        return true;
    }

    /**
     * Скачать клиентский конфиг.
     */
    // public function download(string $id): ?string
    // {
    //     $client = $this->show($id);

    //     if ($client === null) {
    //         return null;
    //     }

    //     return $this->wireGuard->buildClientConfig($client);
    // }

    /**
     * Проверить существование клиента.
     */
    private function exists(string $name): bool
    {
        foreach ($this->all() as $client) {
            if (strcasecmp($client['name'], $name) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получить свободный IP.
     */
    private function getFreeIp(): string
    {
        $used = [];

        foreach ($this->all() as $client) {
            $used[] = $client['ip'];
        }

        for ($i = 2; $i <= 254; $i++) {

            $ip = "10.0.0.$i";

            if (!in_array($ip, $used, true)) {
                return $ip;
            }
        }

        throw new \RuntimeException('Свободных IP нет.');
    }

    /**
     * Проверка входных данных.
     */
    private function validate(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Не указано имя клиента.');
        }
    }

    /**
     * Генерация приватного ключа.
     * Пока заглушка.
     */
    private function generatePrivateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Генерация публичного ключа.
     * Пока заглушка.
     */
    private function generatePublicKey(): string
    {
        return hash('sha256', random_bytes(32));
    }
}
