<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class WireGuardSetupService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly CommandService $command,
    ) {}

    /**
     * Проверяет состояние WireGuard.
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        return [
            'wireGuard' => [
                'installed' => $this->isWireGuardInstalled(),
                'version' => $this->getWireGuardVersion(),
            ],

            'wgQuick' => [
                'installed' => $this->isWgQuickInstalled(),
            ],

            'packageManager' => $this->detectPackageManager(),

            'interface' => [
                'running' => $this->isRunning(),
            ],

            'config' => [
                'exists' => $this->configExists(),
                'readable' => $this->configReadable(),
            ],

            'clients' => [
                'exists' => $this->clientsDirectoryExists(),
                'writable' => $this->clientsDirectoryWritable(),
            ],
        ];
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
        foreach ([
                'apt',
                'dnf',
                'yum',
                'apk',
                'pacman',
                'zypper',
        ] as $manager ) {
            if ($this->command->exists($manager)) {
                return $manager;
            }
        }

        return null;
    }

    /**
     * Проверяет существование конфигурации WireGuard.
     *
     * @return bool
     * true  - Конфигурация существует.
     * false - Конфигурация отсутствует.
     */
    public function configExists(): bool
    {
        return file_exists(
            $this->settings->get(
                SettingsService::SETTING_CONFIG_PATH,
                '/etc/wireguard/wg0.conf'
            )
        );
    }

    /**
     * Проверяет доступность конфигурации WireGuard для чтения.
     *
     * @return bool
     * true  - Конфигурация доступна для чтения.
     * false - Конфигурация недоступна для чтения.
     */
    public function configReadable(): bool
    {
        return is_readable(
            $this->settings->get(
                SettingsService::SETTING_CONFIG_PATH,
                '/etc/wireguard/wg0.conf'
            )
        );
    }

    /**
     * Проверяет существование каталога клиентов.
     *
     * @return bool
     * true  - Каталог существует.
     * false - Каталог отсутствует.
     */
    public function clientsDirectoryExists(): bool
    {
        return is_dir(
            $this->settings->get(
                SettingsService::SETTING_CLIENTS_PATH,
                '/etc/wireguard/clients'
            )
        );
    }

    /**
     * Проверяет доступность каталога клиентов для записи.
     *
     * @return bool
     * true  - Каталог доступен для записи.
     * false - Каталог недоступен для записи.
     */
    public function clientsDirectoryWritable(): bool
    {
        return is_writable(
            $this->settings->get(
                SettingsService::SETTING_CLIENTS_PATH,
                '/etc/wireguard/clients'
            )
        );
    }
}
