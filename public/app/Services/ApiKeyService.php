<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ApiKeyService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {}

    /**
     * Возвращает текущий API-ключ.
     */
    public function get(): string
    {
        return (string) $this->settings->get(
            'apiKey',
            ''
        );
    }

    /**
     * Проверяет API-ключ.
     */
    public function validate(?string $key): bool
    {
        $apiKey = $this->get();

        if ($apiKey === '' || $key === null) {
            return false;
        }

        return hash_equals(
            $apiKey,
            $key
        );
    }

    /**
     * Проверяет, установлен ли API-ключ.
     */
    public function exists(): bool
    {
        return $this->get() !== '';
    }

    /**
     * Создаёт новый API-ключ.
     *
     * Если ключ уже существует — выбрасывает исключение.
     */
    public function generate(): string
    {
        if ($this->exists()) {
            throw new RuntimeException(
                'API-ключ уже существует.'
            );
        }

        return $this->createKey();
    }

    /**
     * Пересоздаёт API-ключ.
     */
    public function rotate(): string
    {
        return $this->createKey();
    }

    /**
     * Удаляет API-ключ.
     */
    public function clear(): void
    {
        $this->settings->set(
            'apiKey',
            ''
        );

        $this->settings->save();
    }

    /**
     * Создаёт и сохраняет новый API-ключ.
     */
    private function createKey(): string
    {
        $apiKey = bin2hex(
            random_bytes(32)
        );

        $this->settings->set(
            'apiKey',
            $apiKey
        );

        $this->settings->save();

        return $apiKey;
    }
}
