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
     * Генерирует новый API-ключ.
     */
    public function generate(): string
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

    /**
     * Проверяет, установлен ли API-ключ.
     */
    public function exists(): bool
    {
        return $this->get() !== '';
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
     * Генерирует новый API-ключ.
     */
    public function rotate(): string
    {
        if (!$this->exists()) {
            throw new RuntimeException(
                'API-ключ отсутствует.'
            );
        }

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
