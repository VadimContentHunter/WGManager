<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ApiKeyService;
use App\Services\Request;
use App\Services\Response;

class ApiKeyController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly ApiKeyService $apiKeys
    ) {}

    /**
     * Возвращает информацию о наличии API-ключа.
     */
    public function show(): void
    {
        $this->response->success([
            'exists' => $this->apiKeys->exists(),
        ]);
    }

    /**
     * Создаёт новый API-ключ.
     */
    public function create(): void
    {
        if ($this->apiKeys->exists()) {
            $this->response->conflict(
                'API-ключ уже существует.'
            );

            return;
        }

        $this->response->success([
            'apiKey' => $this->apiKeys->generate(),
        ], 201);
    }

    /**
     * Заменяет API-ключ.
     */
    public function rotate(): void
    {
        $data = $this->request->require([
            'oldKey',
        ]);

        if (!$this->apiKeys->validate($data['oldKey'])) {
            $this->response->unauthorized(
                'Неверный API-ключ.'
            );

            return;
        }

        $this->response->success([
            'apiKey' => $this->apiKeys->rotate(),
        ]);
    }
}
