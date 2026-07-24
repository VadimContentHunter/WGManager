<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Класс Response.
 *
 * Представляет HTTP-ответ приложения.
 */
class Response
{
    /**
     * Отправляет JSON-ответ.
     */
    public function json(
        mixed $data,
        int $status = 200
    ): void {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }

    public function success(
        mixed $data = null,
        int $status = 200
    ): void {
        $response = [
            'success' => true,
        ];

        if (is_array($data)) {
            $response += $data;
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        $this->json(
            $response,
            $status
        );
    }

    public function error(
        string $message,
        int $status = 400
    ): void {
        $this->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Отправляет ответ 401 Unauthorized.
     */
    public function unauthorized(
        string $message = 'Неверный API ключ.'
    ): void {
        $this->error(
            $message,
            401
        );
    }

    /**
     * Отправляет ответ 403 Forbidden.
     */
    public function forbidden(
        string $message = 'Доступ запрещён.'
    ): void {
        $this->error(
            $message,
            403
        );
    }

    /**
     * Отправляет ответ 404 Not Found.
     */
    public function notFound(
        string $message = 'Не найдено.'
    ): void {
        $this->error(
            $message,
            404
        );
    }

    /**
     * Отправляет HTML.
     */
    public function html(
        string $html,
        int $status = 200
    ): void {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /**
     * Отправляет файл пользователю.
     */
    public function download(
        string $content,
        string $filename,
        string $contentType = 'text/plain'
    ): void {
        http_response_code(200);
        header('Content-Type: ' . $contentType . '; charset=utf-8');
        header(
            sprintf(
                'Content-Disposition: attachment; filename="%s"',
                $filename
            )
        );
        header(
            'Content-Length: ' . strlen($content)
        );

        echo $content;
    }

    /**
     * Отправляет пустой ответ.
     */
    public function noContent(): void
    {
        http_response_code(204);
    }

    /**
     * Перенаправляет пользователя.
     */
    public function redirect(
        string $url,
        int $status = 302
    ): never {
        http_response_code($status);
        header(
            "Location: {$url}"
        );

        exit;
    }

    /**
     * Отправляет ответ 409 Conflict.
     */
    public function conflict(
        string $message = 'Конфликт данных.'
    ): void {
        $this->error(
            $message,
            409
        );
    }

    /**
     * Отправляет ответ 400 Bad Request.
     */
    public function badRequest(
        string $message = 'Некорректный запрос.'
    ): void {
        $this->error(
            $message,
            400
        );
    }

    /**
     * Отправляет ответ 500 Internal Server Error.
     */
    public function internalError(
        string $message = 'Внутренняя ошибка сервера.'
    ): void {
        $this->error(
            $message,
            500
        );
    }
}
