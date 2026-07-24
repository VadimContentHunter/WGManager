<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Response;

class AuthController
{
    public function __construct(
        private readonly Response $response,
    ) {}

    /**
     * Проверяет API-ключ.
     */
    public function check(): void
    {
        $this->response->success([
            'message' => 'Авторизация успешна.',
        ]);
    }
}
