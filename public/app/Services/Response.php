<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Класс Response
 * Представляет HTTP-ответ, включая методы для отправки JSON, HTML и перенаправления.
 */
class Response
{
    /**
     * Отправляет JSON-ответ с указанными данными.
     */
    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

    /**
     * Отправляет HTML-ответ с указанным содержимым.
     */
    public function html(string $html, int $status = 200): void
    {
        http_response_code($status);

        header('Content-Type: text/html; charset=utf-8');

        echo $html;
    }

    /**
     * Перенаправляет пользователя на указанный URL.
     */
    public function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);

        header("Location: {$url}");

        exit;
    }
}

