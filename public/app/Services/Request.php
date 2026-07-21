<?php

declare(strict_types=1);

namespace App\Services;

class Request
{
    public readonly string $method;
    public readonly string $uri;

    public readonly array $route;
    public readonly array $query;
    public readonly array $body;
    public readonly array $files;
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

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->route[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function body(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }
}
