<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Client;
use App\Services\Request;
use App\Services\Response;

/**
 * Контроллер для работы с клиентами.
 * Обрабатывает HTTP-запросы, связанные с CRUD операциями над клиентами.
 */
class ClientController
{
    private Client $client;

    /**
     * Запрос, полученный от клиента.
     */
    public function __construct(
        private Request $request,
        private Response $response,
    ) {
        $this->client = new Client();
    }

    /**
     * GET /api/clients
     * Получает список всех клиентов.
     */
    public function list(): void
    {
        $this->response->json([
            'status' => true,
            'action' => 'list',
        ]);
    }

    /**
     * GET /api/client/{id}
     * Получает данные клиента по его ID.
     */
    public function show(): void
    {
        $this->response->json([
            'status' => true,
            'action' => 'show',
            'id' => $this->request->route('id'),
        ]);
    }

    /**
     * POST /api/client
     * Создает нового клиента.
     */
    public function create(): void
    {
        $this->response->json([
            'status' => true,
            'action' => 'create',
            'body' => $this->request->body,
        ]);
    }

    /**
     * PATCH /api/client/{id}
     * Обновляет данные клиента по его ID.
     */
    public function update(): void
    {
        $this->response->json([
            'status' => true,
            'action' => 'update',
            'id' => $this->request->route('id'),
            'body' => $this->request->body,
        ]);
    }

    /**
     * DELETE /api/client/{id}
     * Удаляет клиента по его ID.
     */
    public function delete(): void
    {
        $this->response->json([
            'status' => true,
            'action' => 'delete',
            'id' => $this->request->route('id'),
        ]);
    }
}

