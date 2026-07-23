<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ClientService;
use App\Services\Request;
use App\Services\Response;

class ClientController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly ClientService $clients,
    ) {}

    /**
     * Получить список клиентов.
     */
    public function list(): void
    {
        $this->response->success(
            $this->clients->all()
        );
    }

    /**
     * Получить клиента.
     */
    public function show(): void
    {
        $client = $this->clients->show(
            $this->request->route('publicKey')
        );

        if ($client === null) {
            $this->response->notFound(
                'Клиент не найден.'
            );

            return;
        }

        $this->response->success($client);
    }

    /**
     * Создать клиента.
     */
    public function create(): void
    {
        $client = $this->clients->create(
            $this->request->body
        );

        $this->response->success(
            $client,
            201
        );
    }

    /**
     * Обновить клиента.
     */
    public function update(): void
    {
        $client = $this->clients->update(
            $this->request->route('publicKey'),
            $this->request->body
        );

        if ($client === null) {
            $this->response->notFound(
                'Клиент не найден.'
            );

            return;
        }

        $this->response->success($client);
    }

    /**
     * Удалить клиента.
     */
    public function delete(): void
    {
        if (
            !$this->clients->delete(
                $this->request->route('publicKey')
            )
        ) {
            $this->response->notFound(
                'Клиент не найден.'
            );

            return;
        }

        $this->response->success([
            'message' => 'Клиент успешно удалён.',
        ]);
    }

    /**
     * Скачать конфигурацию клиента.
     */
    public function download(): void
    {
        $config = $this->clients->download(
            $this->request->route('publicKey')
        );

        if ($config === null) {
            $this->response->notFound(
                'Клиент не найден.'
            );

            return;
        }

        $this->response->download(
            $config,
            'client.conf'
        );
    }
}
