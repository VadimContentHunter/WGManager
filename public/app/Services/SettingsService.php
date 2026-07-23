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
        return $this->settings[$key] ?? $default;
    }

    /**
     * Устанавливает значение настройки.
     */
    public function set(string $key, mixed $value): void
    {
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
