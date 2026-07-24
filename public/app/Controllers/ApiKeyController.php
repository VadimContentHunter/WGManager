<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ApiKeyService;
use App\Services\Request;
use App\Services\Response;
use InvalidArgumentException;
use Throwable;

class ApiKeyController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly ApiKeyService $apiKeys,
    ) {}

    /**
     * Возвращает текущий API-ключ.
     */
    public function show(): void
    {
        $this->response->success([
            'apiKey' => $this->apiKeys->get(),
        ]);
    }

    /**
     * Создаёт новый API-ключ.
     */
    public function create(): void
    {
        try {

            if ($this->apiKeys->exists()) {
                $this->response->conflict(
                    'API-ключ уже существует.'
                );

                return;
            }

            $this->response->success([
                'apiKey' => $this->apiKeys->generate(),
            ], 201);
        } catch (Throwable $e) {

            $this->response->internalError(
                $e->getMessage()
            );
        }
    }

    /**
     * Заменяет API-ключ.
     */
    public function rotate(): void
    {
        try {

            $this->response->success([
                'apiKey' => $this->apiKeys->rotate(),
            ]);
        } catch (Throwable $e) {

            $this->response->internalError(
                $e->getMessage()
            );
        }
    }
}
