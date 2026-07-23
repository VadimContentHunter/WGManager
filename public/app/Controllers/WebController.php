<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Response;

class WebController
{
    public function __construct(
        private readonly Response $response,
    ) {}

    /**
     * Главная страница приложения.
     */
    public function dashboard(): void
    {
        ob_start();
        require __DIR__ . '/../views/dashboard.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }
}
