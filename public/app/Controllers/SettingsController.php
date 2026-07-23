<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Request;
use App\Services\Response;
use App\Services\SettingsService;
use Throwable;

class SettingsController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly SettingsService $settings,
    ) {}

    /**
     * Возвращает настройки.
     */
    public function show(): void
    {
        $this->response->success(
            $this->settings->all()
        );
    }

    /**
     * Сохраняет настройки.
     */
    public function update(): void
    {
        try {

            foreach ($this->request->body as $key => $value) {
                $this->settings->set(
                    $key,
                    $value
                );
            }

            $this->settings->save();

            $this->response->success([
                'message' => 'Настройки сохранены.',
            ]);
        } catch (Throwable $e) {

            $this->response->internalError(
                $e->getMessage()
            );
        }
    }
}
