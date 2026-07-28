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
     * Константы для настроек.
     */
    private const SETTING_SERVER = 'server';
    private const SETTING_SERVER_PORT = 'serverPort';
    private const SETTING_DNS = 'dns';
    private const SETTING_ALLOWED_IPS = 'allowedIps';
    private const SETTING_PERSISTENT_KEEPALIVE = 'persistentKeepalive';
    private const SETTING_CLIENTS_PATH = 'clientsPath';
    private const SETTING_NETWORK = 'network';

    private const DEFAULT_NETWORK = '10.0.0.1/24';
    private const DEFAULT_ALLOWED_IPS = '10.0.0.0/24';
    private const DEFAULT_PORT = '51820';

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
     * Статистика Peer во время выполнения.
     *
     * @var array<string, array{
     *     Handshake:int,
     *     RX:int,
     *     TX:int
     * }>|null
     */
    private ?array $runtimePeers = null;

    /**
     * WireGuardService constructor.
     */
    public function __construct(
        private readonly SettingsService $settings,
        private readonly CommandService $command,
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
        if (
            !is_file($this->configPath)
            || !is_readable($this->configPath)
        ) {
            throw new RuntimeException(
                "Конфигурация WireGuard недоступна: {$this->configPath}"
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
        $this->reset();
        $this->load();
    }

    /**
     * Сбрасывает загруженную конфигурацию.
     */
    private function reset(): void
    {
        $this->interface = [];
        $this->peers = [];
        $this->runtimePeers = null;
    }

    /**
     * Выполняет инициализацию или переинициализацию WireGuard.
     *
     * Генерирует новую конфигурацию сервера и обновляет
     * конфигурации существующих клиентов.
     *
     * @throws RuntimeException
     */
    public function initialize(): void
    {
        if (empty($this->interface) && empty($this->peers)) {
            $this->load();
        }

        $interface = $this->interface;
        $peers = $this->peers;

        try {
            $this->reset();
            $keys = $this->generateKeyPair();
            $this->buildInterface(
                $keys['privateKey']
            );

            $this->peers = $peers;
            $this->ensureClientsDirectory();
            $this->save();
            $this->rebuildClientConfigs();
        } catch (\Throwable $e) {
            $this->interface = $interface;
            $this->peers = $peers;

            throw $e;
        }
    }

    public function isInitialized(): bool
    {
        return
            isset(
                $this->interface[self::INTERFACE_PRIVATE_KEY]
            )
            && $this->interface[self::INTERFACE_PRIVATE_KEY] !== '';
    }

    /**
     * Пересоздает конфигурации всех клиентов.
     *
     * @throws RuntimeException
     */
    public function rebuildClientConfigs(): void
    {
        foreach ($this->getPeers() as $peer) {
            $name = $peer[self::PEER_NAME] ?? '';
            if ($name === '') {
                continue;
            }
            $this->rebuildClientConfig($peer);
        }
    }

    /**
     * Пересоздает конфигурацию клиента.
     *
     * @param array<string, string> $peer
     *
     * @throws RuntimeException
     */
    public function rebuildClientConfig(array $peer): void
    {
        $name = $peer[self::PEER_NAME] ?? '';

        if ($name === '') {
            throw new RuntimeException(
                'Не указано имя клиента.'
            );
        }

        $this->saveClientFile(
            $name,
            'client.conf',
            $this->buildClientConfig($peer)
        );
    }


    /**
     * Заполняет секцию Interface.
     */
    private function buildInterface(string $privateKey): void
    {
        $this->interface = [
            self::INTERFACE_PRIVATE_KEY => $privateKey,
            self::INTERFACE_ADDRESS => $this->settings->get(
                self::SETTING_NETWORK,
                self::DEFAULT_NETWORK
            ),
            self::INTERFACE_LISTEN_PORT => (string) $this->settings->get(
                self::SETTING_SERVER_PORT,
                self::DEFAULT_PORT
            ),
        ];
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
        return array_map(
            fn(array $peer) => $this->enrichPeer($peer),
            $this->peers
        );
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
                return $this->enrichPeer($peer);
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
        foreach ($this->getPeers() as $peer) {

            if (
                ($peer[self::PEER_NAME] ?? '') === $name
            ) {
                return $peer;
            }
        }

        return null;
    }

    /**
     * Возвращает публичный ключ сервера.
     */
    public function getServerPublicKey(): string
    {
        $privateKey = $this->interface[self::INTERFACE_PRIVATE_KEY] ?? '';

        if ($privateKey === '') {
            throw new RuntimeException(
                'Не указан PrivateKey интерфейса.'
            );
        }

        return trim(
            $this->command->run(
                sprintf(
                    'printf %%s %s | wg pubkey',
                    escapeshellarg($privateKey)
                ),
                true
            )
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

    /**
     * Возвращает статистику Peer из WireGuard.
     *
     * @return array<string, array{
     *     Handshake:int,
     *     RX:int,
     *     TX:int
     * }>
     */
    private function getRuntimePeers(): array
    {
        if ($this->runtimePeers !== null) {
            return $this->runtimePeers;
        }

        if (!$this->isRunning()) {
            return $this->runtimePeers = [];
        }

        $output = trim(
            $this->command->runRoot(
                sprintf(
                    'wg show %s dump',
                    escapeshellarg(
                        $this->getInterfaceName()
                    )
                )
            )
        );

        if ($output === '') {
            return $this->runtimePeers = [];
        }

        $lines = preg_split('/\R/', $output);

        if (!$lines) {
            return $this->runtimePeers = [];
        }

        // первая строка — информация об интерфейсе
        array_shift($lines);

        $stats = [];

        foreach ($lines as $line) {
            $parts = explode("\t", $line);

            if (count($parts) < 8) {
                continue;
            }

            $stats[$parts[0]] = [
                'Handshake' => (int) $parts[4],
                'RX' => (int) $parts[5],
                'TX' => (int) $parts[6],
            ];
        }

        $this->runtimePeers = $stats;

        return $this->runtimePeers;
    }

    /**
     * Форматирует время последнего Handshake.
     */
    private function formatHandshake(int $timestamp): string
    {
        if ($timestamp <= 0) {
            return 'Never';
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' sec ago';
        }

        if ($diff < 3600) {
            return floor($diff / 60) . ' min ago';
        }

        if ($diff < 86400) {
            return floor($diff / 3600) . ' hour ago';
        }

        return floor($diff / 86400) . ' day ago';
    }

    /**
     * Форматирует количество байт.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            ++$i;
        }

        return sprintf(
            $i === 0 ? '%d %s' : '%.1f %s',
            $bytes,
            $units[$i]
        );
    }

    /**
     * Возвращает статус Peer.
     */
    private function getPeerStatus(int $handshake): string
    {
        if ($handshake === 0) {
            return 'Never';
        }

        return (time() - $handshake) < 120
            ? 'Online'
            : 'Offline';
    }

    /**
     * Возвращает путь к каталогу клиента.
     *
     * @throws RuntimeException
     */
    public function getClientDirectory(string $name): string
    {
        return $this->getClientsPath()
            . DIRECTORY_SEPARATOR
            . $name;
    }

    /**
     * Возвращает содержимое client.conf.
     *
     * @throws RuntimeException
     */
    public function getClientConfig(string $name): string
    {
        $path = $this->getClientDirectory($name)
            . DIRECTORY_SEPARATOR
            . 'client.conf';

        if (!is_file($path)) {
            throw new RuntimeException(
                'Конфигурация клиента не найдена.'
            );
        }

        $config = file_get_contents($path);

        if ($config === false) {
            throw new RuntimeException(
                'Не удалось прочитать конфигурацию клиента.'
            );
        }

        return $config;
    }

    /**
     * Проверяет, запущен ли интерфейс WireGuard.
     *
     * @return bool
     * true  - Интерфейс запущен.
     * false - Интерфейс остановлен.
     */
    public function isRunning(): bool
    {
        try {

            $this->command->runRoot(
                sprintf(
                    'wg show %s',
                    escapeshellarg(
                        $this->getInterfaceName()
                    )
                )
            );

            return true;
        } catch (RuntimeException) {

            return false;
        }
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

        unset(
            $peer[self::PEER_NAME],
            $peer['Directory'],
            $peer['Status']
        );

        $this->peers[] = $peer;
    }

    /**
     * Создает каталог клиента.
     *
     * @throws RuntimeException
     */
    public function createClientDirectory(string $name): string
    {
        $directory = $this->getClientDirectory($name);

        if (is_dir($directory)) {
            throw new RuntimeException(
                'Каталог клиента уже существует.'
            );
        }

        if (!mkdir($directory, 0755, true)) {
            throw new RuntimeException(
                'Не удалось создать каталог клиента.'
            );
        }

        return $directory;
    }

    /**
     * Переименовывает каталог клиента.
     *
     * @throws RuntimeException
     */
    public function renameClientDirectory(
        string $oldName,
        string $newName
    ): void {
        if ($oldName === $newName) {
            return;
        }

        $oldDirectory = $this->getClientDirectory($oldName);
        $newDirectory = $this->getClientDirectory($newName);

        if (!is_dir($oldDirectory)) {
            throw new RuntimeException(
                'Каталог клиента не найден.'
            );
        }

        if (is_dir($newDirectory)) {
            throw new RuntimeException(
                'Каталог клиента уже существует.'
            );
        }

        if (!rename($oldDirectory, $newDirectory)) {
            throw new RuntimeException(
                'Не удалось переименовать каталог клиента.'
            );
        }
    }

    /**
     * Создает все файлы клиента.
     *
     * @param string $name
     * @param array<string, string> $client
     *
     * @throws RuntimeException
     */
    public function createClientFiles(
        string $name,
        array $client
    ): void {
        $this->createClientDirectory($name);
        try {
            $this->saveClientFile(
                $name,
                'private.key',
                $client[self::PEER_PRIVATE_KEY]
            );

            $this->saveClientFile(
                $name,
                'public.key',
                $client[self::PEER_PUBLIC_KEY]
            );

            $this->saveClientFile(
                $name,
                'client.conf',
                $this->buildClientConfig($client)
            );
        } catch (\Throwable $e) {
            $this->deleteClientDirectory($name);
            throw $e;
        }
    }

    /**
     * Создает каталог клиентов при необходимости.
     *
     * @throws RuntimeException
     */
    private function ensureClientsDirectory(): void
    {
        $path = $this->getClientsPath();

        if (is_dir($path)) {
            return;
        }

        if (!mkdir($path, 0755, true)) {
            throw new RuntimeException(
                'Не удалось создать каталог клиентов.'
            );
        }
    }

    /**
     * Обновляет существующего Peer.
     *
     * @param string $publicKey Публичный ключ Peer.
     * @param array<string, string> $peer Новые данные Peer.
     *
     * @return array<string, string>|null
     * Старые данные Peer или null, если Peer не найден.
     */
    public function updatePeer(
        string $publicKey,
        array $peer
    ): ?array {
        foreach ($this->peers as $index => $item) {

            if (
                ($item[self::PEER_PUBLIC_KEY] ?? '') !== $publicKey
            ) {
                continue;
            }

            unset(
                $peer[self::PEER_NAME],
                $peer['Directory'],
                $peer['Status']
            );

            $oldPeer = $this->peers[$index];

            $this->peers[$index] = $peer;

            return $oldPeer;
        }

        return null;
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
     * Удаляет каталог клиента.
     */
    public function deleteClientDirectory(string $name): void
    {
        $directory = $this->getClientDirectory($name);

        if (!is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) as $file) {

            if ($file === '.' || $file === '..') {
                continue;
            }

            unlink(
                $directory
                    . DIRECTORY_SEPARATOR
                    . $file
            );
        }

        rmdir($directory);
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
        $this->reset();

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
            array_push(
                $config,
                ...$this->buildPeer($peer, false)
            );
        }

        return implode(PHP_EOL, $config) . PHP_EOL;
    }

    /**
     * Формирует клиентскую конфигурацию WireGuard.
     *
     * @param array<string, string> $peer
     *
     * @return string
     */
    public function buildClientConfig(array $peer): string
    {
        $server = trim(
            $this->settings->get(
                self::SETTING_SERVER,
                '127.0.0.1'
            )
        );

        $serverPort = (int) $this->settings->get(
            self::SETTING_SERVER_PORT,
            self::DEFAULT_PORT
        );

        $endpoint = sprintf(
            '%s:%d',
            $server,
            $serverPort
        );

        $allowedIps = trim(
            $this->settings->get(
                self::SETTING_ALLOWED_IPS,
                self::DEFAULT_ALLOWED_IPS
            )
        );

        return implode(PHP_EOL, [
            '[Interface]',
            'PrivateKey = ' . ($peer[self::PEER_PRIVATE_KEY] ?? ''),
            'Address = ' . ($peer[self::PEER_ALLOWED_IPS] ?? ''),
            'DNS = ' . $this->settings->get(
                self::SETTING_DNS,
                '1.1.1.1'
            ),
            '',
            '[Peer]',
            'PublicKey = ' . $this->getServerPublicKey(),
            'Endpoint = ' . $endpoint,
            'AllowedIPs = ' . $allowedIps,
            'PersistentKeepalive = ' . $this->settings->get(
                self::SETTING_PERSISTENT_KEEPALIVE,
                '25'
            ),
            '',
        ]) . PHP_EOL;
    }

    /**
     * Формирует конфигурацию для wg syncconf.
     *
     * В отличие от основного конфигурационного файла содержит
     * только параметры, поддерживаемые командой syncconf.
     */
    private function buildRuntimeConfig(): string
    {
        $config = [];
        $config[] = '[Interface]';
        $config[] = 'PrivateKey = ' . (
            $this->interface[self::INTERFACE_PRIVATE_KEY] ?? ''
        );

        foreach ($this->peers as $peer) {
            $config[] = '';
            array_push(
                $config,
                ...$this->buildPeer($peer, true)
            );
        }

        return implode(PHP_EOL, $config) . PHP_EOL;
    }

    /**
     * Формирует секцию Peer.
     *
     * @param array<string, string> $peer
     * @param bool $runtime
     * true  - Формировать для wg syncconf.
     * false - Формировать для wg0.conf.
     *
     * @return array<int, string>
     */
    private function buildPeer(array $peer, bool $runtime): array
    {
        $config = [
            '[Peer]',
        ];

        foreach ($peer as $key => $value) {

            if (in_array(
                $key,
                [
                    self::PEER_NAME,
                    'Directory',
                    'Status',
                ],
                true
            )) {
                continue;
            }

            if ($key === self::PEER_PRIVATE_KEY) {
                continue;
            }

            $config[] = "{$key} = {$value}";
        }

        return $config;
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
     * Сохраняет файл клиента.
     *
     * @throws RuntimeException
     */
    public function saveClientFile(
        string $name,
        string $filename,
        string $content
    ): void {
        $path = $this->getClientDirectory($name)
            . DIRECTORY_SEPARATOR
            . $filename;

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException(
                "Не удалось сохранить {$filename}."
            );
        }
    }

    /**
     * Применяет конфигурацию WireGuard.
     *
     * @throws RuntimeException
     */
    public function apply(): void
    {
        $tempFile = tempnam(
            sys_get_temp_dir(),
            'wgmanager_'
        );

        if ($tempFile === false) {
            throw new RuntimeException(
                'Не удалось создать временный файл.'
            );
        }

        try {
            if (
                file_put_contents(
                    $tempFile,
                    $this->buildRuntimeConfig()
                ) === false
            ) {
                throw new RuntimeException(
                    'Не удалось записать временную конфигурацию.'
                );
            }

            $this->command->runRoot(
                sprintf(
                    'wg syncconf %s %s',
                    escapeshellarg($this->getInterfaceName()),
                    escapeshellarg($tempFile)
                )
            );
        } finally {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Генерирует пару ключей WireGuard.
     *
     * @return array{
     *     privateKey: string,
     *     publicKey: string
     * }
     *
     * @throws RuntimeException
     */
    public function generateKeyPair(): array
    {
        $privateKey = trim(
            $this->command->run('wg genkey')
        );

        $publicKey = trim(
            $this->command->run(
                sprintf(
                    'printf %%s %s | wg pubkey',
                    escapeshellarg($privateKey)
                ),
                true
            )
        );

        return [
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
        ];
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
     * Возвращает путь к каталогу клиентов.
     */
    private function getClientsPath(): string
    {
        return rtrim(
            $this->settings->get(
                self::SETTING_CLIENTS_PATH,
                dirname($this->configPath) . '/clients'
            ),
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * Ищет каталог клиента по PublicKey.
     */
    private function findClientDirectory(
        string $publicKey
    ): ?string {
        $clientsPath = $this->getClientsPath();

        if (!is_dir($clientsPath)) {
            return null;
        }

        foreach (scandir($clientsPath) as $directory) {
            if ($directory === '.' || $directory === '..') {
                continue;
            }

            $path = $clientsPath . DIRECTORY_SEPARATOR . $directory;
            if (!is_dir($path)) {
                continue;
            }

            $keyFile = $path . DIRECTORY_SEPARATOR . 'public.key';
            if (!is_file($keyFile)) {
                continue;
            }

            $key = trim(file_get_contents($keyFile) ?: '');
            if ($key === $publicKey) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Дополняет данные Peer информацией из каталога клиента
     * и статистикой WireGuard.
     *
     * @param array<string, string> $peer
     *
     * @return array<string, string>
     */
    private function enrichPeer(array $peer): array
    {
        $directory = $this->findClientDirectory(
            $peer[self::PEER_PUBLIC_KEY] ?? ''
        );

        if ($directory === null) {
            $peer[self::PEER_NAME] = '';
            $peer['Directory'] = '';
            $peer['Status'] = 'Некорректный';
            $peer['Handshake'] = '—';
            $peer['RX'] = '—';
            $peer['TX'] = '—';

            return $peer;
        }

        $peer[self::PEER_NAME] = basename($directory);
        $peer['Directory'] = $directory;

        $runtime = $this->getRuntimePeers();
        $stats = $runtime[$peer[self::PEER_PUBLIC_KEY] ?? ''] ?? null;

        if ($stats === null) {
            $peer['Status'] = 'Offline';
            $peer['Handshake'] = 'Never';
            $peer['RX'] = '0 B';
            $peer['TX'] = '0 B';

            return $peer;
        }

        $peer['Status'] = $this->getPeerStatus(
            $stats['Handshake']
        );

        $peer['Handshake'] = $this->formatHandshake(
            $stats['Handshake']
        );

        $peer['RX'] = $this->formatBytes(
            $stats['RX']
        );

        $peer['TX'] = $this->formatBytes(
            $stats['TX']
        );

        return $peer;
    }
}
