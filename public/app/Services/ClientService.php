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
     * Преобразует данные API во внутреннюю модель.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function fromApi(array $data): array
    {
        $map = [
            'name'       => 'Name',
            'publicKey'  => 'PublicKey',
            'privateKey' => 'PrivateKey',
            'allowedIps' => 'AllowedIPs',
        ];

        $result = [];

        foreach ($data as $key => $value) {
            $result[$map[$key] ?? $key] = $value;
        }

        return $result;
    }

    /**
     * Преобразует внутреннюю модель в API.
     *
     * @param array<string, mixed> $client
     * @return array<string, mixed>
     */
    private function toApi(array $client): array
    {
        $map = [
            'Name'          => 'name',
            'PublicKey'     => 'publicKey',
            'PrivateKey'    => 'privateKey',
            'AllowedIPs'    => 'allowedIps',
            'Directory'     => 'directory',
            'Status'        => 'status',
            'Handshake'     => 'handshake',
            'RX'            => 'rx',
            'TX'            => 'tx',
        ];

        $result = [];

        foreach ($client as $key => $value) {
            $result[$map[$key] ?? $key] = $value;
        }

        return $result;
    }

    /**
     * Получить список клиентов.
     */
    public function all(): array
    {
        return array_map(
            fn(array $client) => $this->toApi($client),
            $this->wireGuard->getPeers()
        );
    }

    /**
     * Получить клиента.
     */
    public function show(string $publicKey): ?array
    {
        $client = $this->wireGuard->getPeer($publicKey);

        if ($client === null) {
            return null;
        }

        return $this->toApi($client);
    }

    /**
     * Создать клиента.
     */
    public function create(array $data): array
    {
        $data = $this->fromApi($data);
        $this->validate($data['Name'] ?? '');
        $name = trim($data['Name']);
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

            if ($this->wireGuard->isRunning()) {
                $this->wireGuard->apply();
            }
        } catch (\Throwable $e) {
            $this->wireGuard->removePeer(
                $client['PublicKey']
            );

            $this->wireGuard->deleteClientDirectory(
                $name
            );

            throw $e;
        }

        return $this->toApi(
            $this->wireGuard->getPeer(
                $client['PublicKey']
            )
        );
    }

    /**
     * Обновить клиента.
     */
    public function update(string $publicKey, array $data): ?array
    {
        $data = $this->fromApi($data);

        $client = $this->wireGuard->getPeer($publicKey);

        if ($client === null) {
            return null;
        }

        $oldName = $client['Name'];
        $name = trim($data['Name'] ?? $oldName);

        $this->validate($name);

        if (
            $name !== $oldName
            && $this->wireGuard->hasPeerName($name)
        ) {
            throw new RuntimeException(
                'Клиент с таким именем уже существует.'
            );
        }

        $data['Name'] = $name;

        $updated = array_merge(
            $client,
            $data
        );

        /** @var array<string, string>|null $oldPeer */
        $oldPeer = null;

        try {
            if ($oldName !== $name) {
                $this->wireGuard->renameClientDirectory(
                    $oldName,
                    $name
                );
            }

            $oldPeer = $this->wireGuard->updatePeer(
                $publicKey,
                $updated
            );

            $this->wireGuard->save();

            $this->wireGuard->rebuildClientConfig(
                $updated
            );

            if ($this->wireGuard->isRunning()) {
                $this->wireGuard->apply();
            }
        } catch (\Throwable $e) {
            if ($oldPeer !== null) {
                $this->wireGuard->updatePeer(
                    $publicKey,
                    $oldPeer
                );
            }

            if ($oldName !== $name) {
                try {
                    $this->wireGuard->renameClientDirectory(
                        $name,
                        $oldName
                    );
                } catch (\Throwable) {
                }
            }

            throw $e;
        }

        return $this->toApi(
            $this->wireGuard->getPeer(
                $publicKey
            )
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

            if ($this->wireGuard->isRunning()) {
                $this->wireGuard->apply();
            }
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
     * Проверка имени клиента.
     */
    private function validate(
        string $name
    ): void {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Не указано имя клиента.'
            );
        }

        if (
            !preg_match(
                '/^[A-Za-z0-9_-]{1,32}$/',
                $name
            )
        ) {
            throw new InvalidArgumentException(
                'Допустимы только латинские буквы, цифры, "-", "_" (до 32 символов).'
            );
        }
    }

    /**
     * Скачать клиентскую конфигурацию.
     */
    public function download(string $publicKey): ?string {
        $client = $this->wireGuard->getPeer($publicKey);

        if ($client === null) {
            return null;
        }

        return $this->wireGuard->getClientConfig(
            $client['Name']
        );
    }
}
