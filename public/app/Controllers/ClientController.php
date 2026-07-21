<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Request;
use App\Services\Response;

class ClientController
{
    public function __construct(
        private Request $request,
        private Response $response,
    ) {}

    /**
     * GET /api/clients
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
