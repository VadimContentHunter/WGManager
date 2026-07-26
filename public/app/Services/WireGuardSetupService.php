<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class WireGuardSetupService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly CommandService $command,
        private readonly WireGuardService $wireGuard,
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

            'permissions' => [
                'root' => $this->hasRootPermissions(),
            ],
        ];
    }

    /**
     * Выполняет инициализацию WireGuard.
     *
     * Генерирует новую конфигурацию сервера
     * и обновляет конфигурации клиентов.
     *
     * @throws RuntimeException
     */
    public function initialize(): void
    {
        $this->wireGuard->initialize();
    }

    /**
     * Запускает интерфейс WireGuard.
     *
     * @throws RuntimeException
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            return;
        }

        $this->command->runRoot(
            sprintf(
                'wg-quick up %s',
                $this->getInterfaceName()
            )
        );
    }

    /**
     * Останавливает интерфейс WireGuard.
     *
     * @throws RuntimeException
     */
    public function stop(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->command->runRoot(
            sprintf(
                'wg-quick down %s',
                $this->getInterfaceName()
            )
        );
    }

    /**
     * Перезапускает интерфейс WireGuard.
     *
     * @throws RuntimeException
     */
    public function restart(): void
    {
        $this->stop();
        $this->start();
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

            $this->command->runRoot(
                'wg show'
            );

            return true;
        } catch (RuntimeException) {

            return false;
        }
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

    /**
     * Возвращает имя интерфейса WireGuard.
     */
    private function getInterfaceName(): string
    {
        return pathinfo(
            $this->settings->get(
                SettingsService::SETTING_CONFIG_PATH,
                '/etc/wireguard/wg0.conf'
            ),
            PATHINFO_FILENAME
        );
    }

    /**
     * Проверяет наличие прав администратора.
     *
     * @return bool
     * true  - Команды с правами root доступны.
     * false - Команды с правами root недоступны.
     */
    public function hasRootPermissions(): bool
    {
        try {

            $this->command->runRoot('wg --version');

            return true;
        } catch (RuntimeException) {

            return false;
        }
    }
}
