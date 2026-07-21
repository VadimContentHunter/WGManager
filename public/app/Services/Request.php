<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Класс Request
 * Представляет HTTP-запрос, включая данные из URI, заголовков, тела и т.д.
 */
class Request
{
    /**
     * Метод HTTP-запроса.
     */
    public readonly string $method;

    /**
     * URI HTTP-запроса.
     */
    public readonly string $uri;

    /**
     * Данные, извлеченные из URI (например, параметры маршрута).
     *
     * @var array<string, string>
     */
    public readonly array $route;

    /**
     * Загруженные параметры из строки запроса.
     *
     * @var array<string, string>
     */
    public readonly array $query;

    /**
     * Данные, извлеченные из тела запроса.
     *
     * @var array<string, string>
     */
    public readonly array $body;

    /**
     * Загруженные файлы.
     *
     * @var array<string, array<string, string>>
     */
    public readonly array $files;

    /**
     * Заголовки HTTP-запроса.
     *
     * @var array<string, string>
     */
    public readonly array $headers;

    public function __construct(array $route = [])
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->route = $route;
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->files = $_FILES;
        $this->headers = getallheaders();
    }

    private function parseBody(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            return json_decode(
                file_get_contents('php://input'),
                true
            ) ?? [];
        }

        parse_str(file_get_contents('php://input'), $data);
        return $data;
    }

    /**
     * Получает значение из $route по ключу.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->route[$key] ?? $default;
    }

    /**
     * Получает значение из $query по ключу.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Получает значение из $body по ключу.
     */
    public function body(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Получает значение из $headers по ключу.
     */
    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }
}

