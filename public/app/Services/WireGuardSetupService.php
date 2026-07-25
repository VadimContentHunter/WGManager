<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class WireGuardSetupService
{
    public function __construct(
        private readonly CommandService $command,
    ) {}

    /**
     * Возвращает информацию о системе.
     *
     * @return array<string, mixed>
     */
    public function getInfo(): array
    {
        return [
            'wireGuard' => [
                'installed' => $this->isWireGuardInstalled(),
                'version'   => $this->getWireGuardVersion(),
            ],

            'wgQuick' => [
                'installed' => $this->isWgQuickInstalled(),
            ],

            'packageManager' => $this->detectPackageManager(),

            'interface' => [
                'running' => $this->isRunning(),
            ],
        ];
    }

    /**
     * Проверяет состояние WireGuard.
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        return [];
    }

    /**
     * Выполняет первоначальную настройку WireGuard.
     */
    public function initialize(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Устанавливает WireGuard.
     */
    public function install(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Обновляет WireGuard.
     */
    public function update(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Запускает интерфейс.
     */
    public function start(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Останавливает интерфейс.
     */
    public function stop(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Перезапускает интерфейс.
     */
    public function restart(): void
    {
        throw new RuntimeException(
            'Метод пока не реализован.'
        );
    }

    /**
     * Проверяет установлен ли WireGuard.
     *
     * @return bool
     * true  - WireGuard установлен.
     * false - WireGuard не установлен.
     */
    public function isWireGuardInstalled(): bool
    {
        return $this->command->exists('wg');
    }

    /**
     * Возвращает версию WireGuard.
     *
     * @return string|null
     */
    public function getWireGuardVersion(): ?string
    {
        if (!$this->isWireGuardInstalled()) {
            return null;
        }

        $version = $this->command->run(
            'wg --version'
        );

        if (
            preg_match(
                '/wireguard-tools\s+([^\s]+)/',
                $version,
                $matches
            )
        ) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Проверяет наличие wg-quick.
     *
     * @return bool
     * true  - wg-quick установлен.
     * false - wg-quick отсутствует.
     */
    public function isWgQuickInstalled(): bool
    {
        return $this->command->exists('wg-quick');
    }

    /**
     * Проверяет запущен ли WireGuard.
     *
     * @return bool
     * true  - Интерфейс WireGuard запущен.
     * false - Интерфейс WireGuard остановлен.
     */
    public function isRunning(): bool
    {
        try {

            $this->command->run(
                'wg show'
            );

            return true;
        } catch (RuntimeException) {

            return false;
        }
    }

    /**
     * Определяет пакетный менеджер.
     *
     * @return string|null
     */
    public function detectPackageManager(): ?string
    {
        foreach (
            [
                'apt',
                'dnf',
                'yum',
                'apk',
                'pacman',
                'zypper',
            ] as $manager
        ) {
            if ($this->command->exists($manager)) {
                return $manager;
            }
        }

        return null;
    }
}
