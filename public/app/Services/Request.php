<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

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
     * Данные, извлеченные из URI.
     *
     * @var array<string, string>
     */
    public readonly array $route;

    /**
     * GET-параметры.
     *
     * @var array<string, mixed>
     */
    public readonly array $query;

    /**
     * Данные тела запроса.
     *
     * @var array<string, mixed>
     */
    public readonly array $body;

    /**
     * Загруженные файлы.
     *
     * @var array<string, mixed>
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

    /**
     * Разбирает тело запроса.
     */
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

        parse_str(
            file_get_contents('php://input'),
            $data
        );

        return $data;
    }

    /**
     * Возвращает параметр маршрута.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->route[$key] ?? $default;
    }

    /**
     * Возвращает GET-параметр.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Возвращает параметр тела запроса.
     */
    public function body(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Возвращает HTTP-заголовок.
     */
    public function header(
        string $key,
        mixed $default = null
    ): mixed {
        foreach ($this->headers as $name => $value) {

            if (strcasecmp($name, $key) === 0) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Проверяет наличие обязательных полей.
     *
     * @return array<string, mixed>
     * 
     * @throws InvalidArgumentException
     */
    public function require(array $fields): array
    {
        foreach ($fields as $field) {
            if (
                !array_key_exists($field, $this->body)
                || $this->body[$field] === null
                || $this->body[$field] === ''
            ) {
                throw new InvalidArgumentException(
                    "Поле '{$field}' обязательно."
                );
            }
        }

        return $this->body;
    }
}
