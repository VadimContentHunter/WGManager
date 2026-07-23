<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Сервис для работы с конфигурацией WireGuard.
 *
 * Отвечает за:
 * - чтение конфигурации;
 * - изменение Peer;
 * - сохранение конфигурации;
 * - применение изменений.
 */
class WireGuardService
{
    /**
     * Ключи Interface.
     */
    private const INTERFACE_PRIVATE_KEY = 'PrivateKey';
    private const INTERFACE_ADDRESS = 'Address';
    private const INTERFACE_LISTEN_PORT = 'ListenPort';

    /**
     * Ключи Peer.
     */
    private const PEER_NAME = 'Name';
    private const PEER_PUBLIC_KEY = 'PublicKey';
    private const PEER_PRIVATE_KEY = 'PrivateKey';
    private const PEER_PRESHARED_KEY = 'PresharedKey';
    private const PEER_ALLOWED_IPS = 'AllowedIPs';
    private const PEER_ENDPOINT = 'Endpoint';
    private const PEER_PERSISTENT_KEEPALIVE = 'PersistentKeepalive';

    /**
     * Путь к конфигурации WireGuard.
     */
    private string $configPath;

    /**
     * Настройки интерфейса.
     *
     * @var array<string, string>
     */
    private array $interface = [];

    /**
     * Список Peer.
     *
     * @var array<int, array<string, string>>
     */
    private array $peers = [];

    /**
     * WireGuardService constructor.
     */
    public function __construct(
        private readonly SettingsService $settings,
    ) {
        $this->configPath = $this->settings->get(
            'configPath',
            '/etc/wireguard/wg0.conf'
        );
    }

    /**
     * Загружает конфигурацию WireGuard.
     *
     * @throws RuntimeException
     */
    public function load(): void
    {
        if (!file_exists($this->configPath)) {
            throw new RuntimeException(
                "Конфигурация WireGuard не найдена: {$this->configPath}"
            );
        }

        $lines = file(
            $this->configPath,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lines === false) {
            throw new RuntimeException(
                'Не удалось прочитать конфигурацию WireGuard.'
            );
        }

        $this->parse($lines);
    }

    /**
     * Повторно загружает конфигурацию.
     *
     * @throws RuntimeException
     */
    public function reload(): void
    {
        $this->interface = [];
        $this->peers = [];

        $this->load();
    }

    /**
     * Возвращает настройки интерфейса.
     *
     * @return array<string, string>
     */
    public function getInterface(): array
    {
        return $this->interface;
    }

    /**
     * Возвращает список Peer.
     *
     * @return array<int, array<string, string>>
     */
    public function getPeers(): array
    {
        return $this->peers;
    }

    /**
     * Возвращает Peer по публичному ключу.
     *
     * @param string $publicKey Публичный ключ.
     *
     * @return array<string, string>|null
     */
    public function getPeer(string $publicKey): ?array
    {
        foreach ($this->peers as $peer) {

            if (
                ($peer[self::PEER_PUBLIC_KEY] ?? '') === $publicKey
            ) {
                return $peer;
            }
        }

        return null;
    }

    /**
     * Возвращает Peer по имени.
     *
     * @param string $name Имя клиента.
     *
     * @return array<string, string>|null
     */
    public function getPeerByName(string $name): ?array
    {
        foreach ($this->peers as $peer) {

            if (
                ($peer[self::PEER_NAME] ?? '') === $name
            ) {
                return $peer;
            }
        }

        return null;
    }

    /**
     * Проверяет существование Peer по публичному ключу.
     *
     * @param string $publicKey Публичный ключ.
     *
     * @return bool
     * true  - Peer существует.
     * false - Peer отсутствует.
     */
    public function hasPeer(string $publicKey): bool
    {
        return $this->getPeer($publicKey) !== null;
    }

    /**
     * Проверяет существование Peer по имени.
     *
     * @param string $name Имя клиента.
     *
     * @return bool
     * true  - Peer существует.
     * false - Peer отсутствует.
     */
    public function hasPeerName(string $name): bool
    {
        return $this->getPeerByName($name) !== null;
    }

    /**
     * Добавляет нового Peer.
     *
     * @param array<string, string> $peer Данные Peer.
     *
     * @return void
     *
     * @throws RuntimeException Если Peer уже существует.
     */
    public function addPeer(array $peer): void
    {
        $publicKey = $peer[self::PEER_PUBLIC_KEY] ?? '';

        if ($publicKey === '') {
            throw new RuntimeException('Не указан PublicKey.');
        }

        if ($this->hasPeer($publicKey)) {
            throw new RuntimeException('Peer уже существует.');
        }

        $this->peers[] = $peer;
    }

    /**
     * Обновляет существующего Peer.
     *
     * @param string $publicKey Публичный ключ Peer.
     * @param array<string, string> $peer Новые данные Peer.
     *
     * @return bool
     * true  - Peer успешно обновлен.
     * false - Peer не найден.
     */
    public function updatePeer(string $publicKey, array $peer): bool
    {
        foreach ($this->peers as $index => $item) {

            if (
                ($item[self::PEER_PUBLIC_KEY] ?? '') !== $publicKey
            ) {
                continue;
            }

            $this->peers[$index] = $peer;

            return true;
        }

        return false;
    }

    /**
     * Удаляет Peer.
     *
     * @param string $publicKey Публичный ключ Peer.
     *
     * @return bool
     * true  - Peer успешно удален.
     * false - Peer не найден.
     */
    public function removePeer(string $publicKey): bool
    {
        foreach ($this->peers as $index => $peer) {

            if (
                ($peer[self::PEER_PUBLIC_KEY] ?? '') !== $publicKey
            ) {
                continue;
            }

            unset($this->peers[$index]);

            $this->peers = array_values($this->peers);

            return true;
        }

        return false;
    }

    /**
     * Удаляет всех Peer.
     *
     * @return void
     */
    public function clearPeers(): void
    {
        $this->peers = [];
    }

    /**
     * Разбирает конфигурацию WireGuard.
     *
     * @param array<int, string> $lines Строки файла конфигурации.
     *
     * @return void
     */
    private function parse(array $lines): void
    {
        $this->interface = [];
        $this->peers = [];

        $section = null;

        /** @var array<string, string> $peer */
        $peer = [];

        foreach ($lines as $line) {

            $line = trim($line);

            if ($line === '') {
                continue;
            }

            switch ($line) {

                case '[Interface]':

                    if (!empty($peer)) {
                        $this->peers[] = $peer;
                        $peer = [];
                    }

                    $section = 'interface';

                    continue 2;

                case '[Peer]':

                    if (!empty($peer)) {
                        $this->peers[] = $peer;
                    }

                    $peer = [];

                    $section = 'peer';

                    continue 2;
            }

            if (str_starts_with($line, '#')) {

                if ($section === 'peer') {
                    $peer[self::PEER_NAME] = trim(substr($line, 1));
                }

                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map(
                'trim',
                explode('=', $line, 2)
            );

            if ($section === 'interface') {
                $this->interface[$key] = $value;
                continue;
            }

            if ($section === 'peer') {
                $peer[$key] = $value;
            }
        }

        if (!empty($peer)) {
            $this->peers[] = $peer;
        }
    }

    /**
     * Формирует содержимое конфигурации WireGuard.
     *
     * @return string
     */
    private function build(): string
    {
        $config = [];

        $config[] = '[Interface]';

        foreach ($this->interface as $key => $value) {
            $config[] = "{$key} = {$value}";
        }

        foreach ($this->peers as $peer) {

            $config[] = '';
            $config[] = '[Peer]';

            if (!empty($peer[self::PEER_NAME])) {
                $config[] = '# ' . $peer[self::PEER_NAME];
            }

            foreach ($peer as $key => $value) {

                if ($key === self::PEER_NAME) {
                    continue;
                }

                $config[] = "{$key} = {$value}";
            }
        }

        return implode(PHP_EOL, $config) . PHP_EOL;
    }

    /**
     * Сохраняет конфигурацию WireGuard.
     *
     * @return void
     *
     * @throws RuntimeException Если не удалось сохранить файл.
     */
    public function save(): void
    {
        $result = file_put_contents(
            $this->configPath,
            $this->build()
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось сохранить конфигурацию WireGuard.'
            );
        }
    }

    /**
     * Применяет конфигурацию WireGuard.
     *
     * @return void
     *
     * @throws RuntimeException Если применение завершилось ошибкой.
     */
    public function apply(): void
    {
        $interface = escapeshellarg(
            $this->getInterfaceName()
        );

        $this->executeCommand(
            sprintf(
                'wg syncconf %s <(wg-quick strip %s)',
                $interface,
                $interface
            ),
            true
        );
    }

    /**
     * Возвращает количество Peer.
     *
     * @return int
     */
    public function getPeersCount(): int
    {
        return count($this->peers);
    }

    /**
     * Проверяет наличие Peer.
     *
     * @return bool
     * true  - Список Peer не пуст.
     * false - Peer отсутствуют.
     */
    public function hasPeers(): bool
    {
        return !empty($this->peers);
    }

    /**
     * Выполняет системную команду.
     *
     * @param string $command Команда.
     * @param bool $useBash Использовать Bash.
     *
     * @return string Результат выполнения команды.
     *
     * @return bool
     * true  - Команда будет выполнена через Bash.
     * false - Команда будет выполнена стандартной оболочкой.
     *
     * @throws RuntimeException
     */
    private function executeCommand(
        string $command,
        bool $useBash = false
    ): string {
        if ($useBash) {
            $command = sprintf(
                'bash -c %s',
                escapeshellarg($command)
            );
        }

        $output = [];
        $exitCode = 0;

        exec(
            $command . ' 2>&1',
            $output,
            $exitCode
        );

        if ($exitCode !== 0) {
            throw new RuntimeException(
                implode(PHP_EOL, $output)
            );
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Возвращает имя интерфейса WireGuard.
     *
     * @return string
     */
    private function getInterfaceName(): string
    {
        return pathinfo(
            $this->configPath,
            PATHINFO_FILENAME
        );
    }
}
