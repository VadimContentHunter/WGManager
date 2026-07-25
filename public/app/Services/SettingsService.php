<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class SettingsService
{
    /**
     * Путь к файлу настроек.
     */
    private string $file;

    /**
     * Загруженные настройки.
     */
    private array $settings = [];

    private const ALLOWED_KEYS = [
        'apiKey',
        'network',
        'server',
        'serverPort',
        'dns',
        'configPath',
        'clientsPath',
        'allowedIps',
        'persistentKeepalive',
    ];

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? dirname(__DIR__, 2) . '/config/settings.json';

        $this->load();
    }

    /**
     * Загружает настройки.
     */
    public function load(): void
    {
        if (!file_exists($this->file)) {
            throw new RuntimeException("Файл настроек не найден: {$this->file}");
        }

        $json = file_get_contents($this->file);

        if ($json === false) {
            throw new RuntimeException('Не удалось прочитать settings.json.');
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException('Некорректный JSON в settings.json.');
        }

        $this->settings = $data;
    }

    /**
     * Сохраняет настройки.
     */
    public function save(): void
    {
        $json = json_encode(
            $this->settings,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('Ошибка формирования JSON.');
        }

        file_put_contents($this->file, $json);
    }

    /**
     * Возвращает все настройки.
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * Возвращает значение настройки.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->settings)) {
            return $default;
        }

        $value = $this->settings[$key];

        if (
            $default !== null
            && is_string($default)
            && is_string($value)
            && trim($value) === ''
        ) {
            return $default;
        }

        return $value;
    }

    /**
     * Устанавливает значение настройки.
     */
    public function set(string $key, mixed $value): void
    {
        if (!in_array($key, self::ALLOWED_KEYS, true)) {
            throw new RuntimeException(
                "Неизвестный параметр настроек: {$key}"
            );
        }

        switch ($key) {
            case 'server':
                $value = trim((string) $value);
                if ($value === '') {
                    $value = '127.0.0.1';
                }
                break;

            case 'serverPort':
                $value = trim((string) $value);
                if ($value === '') {
                    $value = 51820;
                    break;
                }

                $value = (int) $value;
                if ($value < 1 || $value > 65535) {
                    throw new RuntimeException(
                        'Порт должен быть в диапазоне от 1 до 65535.'
                    );
                }
                break;

            case 'configPath':
                $value = trim((string) $value);
                if (!is_file($value)) {
                    throw new RuntimeException(
                        "Конфигурация WireGuard не найдена: {$value}"
                    );
                }
                break;
        }

        $this->settings[$key] = $value;
    }

    /**
     * Проверяет существование параметра.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Удаляет параметр.
     */
    public function remove(string $key): void
    {
        unset($this->settings[$key]);
    }

    /**
     * Возвращает путь к settings.json.
     */
    public function path(): string
    {
        return $this->file;
    }
}
