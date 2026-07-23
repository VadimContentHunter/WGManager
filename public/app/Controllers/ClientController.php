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

    public function list(): void
    {
        $this->response->json(
            $this->client->all()
        );
    }

    public function show(): void
    {
        $client = $this->client->show(
            $this->request->route('id')
        );

        $this->response->json($client);
    }

    public function create(): void
    {
        $client = $this->client->create(
            $this->request->body
        );

        $this->response->json($client);
    }

    public function update(): void
    {
        $client = $this->client->update(
            $this->request->route('id'),
            $this->request->body
        );

        $this->response->json($client);
    }

    public function delete(): void
    {
        $result = $this->client->delete(
            $this->request->route('id')
        );

        $this->response->json([
            'deleted' => $result
        ]);
    }
}
