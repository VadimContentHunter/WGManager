<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Класс Autoloader
 * Обрабатывает автоматическую загрузку классов в приложении.
 */
class Autoloader
{
    /**
     * Регистрирует автозагрузчик с помощью SPL.
     * Этот метод настраивает механизм автозагрузки для автоматической загрузки файлов классов.
     */
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {

            $prefix = 'App\\';

            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));

            $file = __DIR__ . '/../' .
                str_replace('\\', DIRECTORY_SEPARATOR, $relative)
                . '.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}

