<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class ClientService
{
    public function __construct(
        private readonly WireGuardService $wireGuard,
    ) {
        $this->wireGuard->load();
    }

    /**
     * Получить список клиентов.
     */
    public function all(): array
    {
        return $this->wireGuard->getPeers();
    }

    /**
     * Получить клиента.
     */
    public function show(string $publicKey): ?array
    {
        return $this->wireGuard->getPeer($publicKey);
    }

    /**
     * Создать клиента.
     */
    public function create(array $data): array
    {
        $this->validate($data);

        $name = trim($data['name']);

        if ($this->wireGuard->hasPeerName($name)) {
            throw new RuntimeException(
                'Клиент уже существует.'
            );
        }

        $keys = $this->wireGuard->generateKeyPair();

        $client = [
            'Name'       => $name,
            'PrivateKey' => $keys['privateKey'],
            'PublicKey'  => $keys['publicKey'],
            'AllowedIPs' => $this->getFreeIp() . '/32',
        ];

        try {

            $this->wireGuard->addPeer($client);

            $this->wireGuard->createClientFiles(
                $name,
                $client
            );

            $this->wireGuard->save();

            $this->wireGuard->apply();
        } catch (\Throwable $e) {

            $this->wireGuard->removePeer(
                $client['PublicKey']
            );

            $this->wireGuard->deleteClientDirectory(
                $name
            );

            throw $e;
        }

        return $this->wireGuard->getPeer(
            $client['PublicKey']
        );
    }

    /**
     * Обновить клиента.
     */
    public function update(
        string $publicKey,
        array $data
    ): ?array {

        $client = $this->wireGuard->getPeer(
            $publicKey
        );

        if ($client === null) {
            return null;
        }

        if (
            isset($data['Name'])
            && $data['Name'] !== ($client['Name'] ?? '')
            && $this->wireGuard->hasPeerName($data['Name'])
        ) {
            throw new RuntimeException(
                'Клиент с таким именем уже существует.'
            );
        }

        $client = array_merge(
            $client,
            $data
        );

        $this->wireGuard->updatePeer(
            $publicKey,
            $client
        );
        $this->wireGuard->save();
        $this->wireGuard->apply();
        
        return $this->wireGuard->getPeer(
            $publicKey
        );
    }

    /**
     * Удалить клиента.
     */
    public function delete(
        string $publicKey
    ): bool {

        $client = $this->wireGuard->getPeer(
            $publicKey
        );

        if ($client === null) {
            return false;
        }

        if (
            !$this->wireGuard->removePeer(
                $publicKey
            )
        ) {
            return false;
        }

        try {

            $this->wireGuard->deleteClientDirectory(
                $client['Name']
            );

            $this->wireGuard->save();

            $this->wireGuard->apply();
        } catch (\Throwable $e) {

            $this->wireGuard->addPeer(
                $client
            );

            throw $e;
        }

        return true;
    }

    /**
     * Получить свободный IP.
     */
    private function getFreeIp(): string
    {
        $used = [];

        foreach (
            $this->wireGuard->getPeers()
            as $peer
        ) {

            if (empty($peer['AllowedIPs'])) {
                continue;
            }

            $used[] = explode(
                '/',
                $peer['AllowedIPs']
            )[0];
        }

        for ($i = 2; $i <= 254; $i++) {
            $ip = "10.0.0.$i";
            if (
                !in_array(
                    $ip,
                    $used,
                    true
                )
            ) {
                return $ip;
            }
        }

        throw new RuntimeException(
            'Свободных IP нет.'
        );
    }

    /**
     * Проверка входных данных.
     */
    private function validate(
        array $data
    ): void {

        if (empty($data['name'])) {
            throw new InvalidArgumentException(
                'Не указано имя клиента.'
            );
        }
    }

    /**
     * Скачать клиентскую конфигурацию.
     */
    public function download(
        string $publicKey
    ): ?string {
        $client = $this->show(
            $publicKey
        );

        if ($client === null) {
            return null;
        }

        return $this->wireGuard->getClientConfig(
            $client['Name']
        );
    }
}
