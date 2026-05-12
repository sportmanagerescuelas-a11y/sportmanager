<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    /**
     * @param array<string,mixed> $data
     */
    public static function render(string $viewName, array $data = []): void
    {
        $viewFile = APP_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewName . '.php';
        if (!is_file($viewFile)) {
            throw new RuntimeException("Vista no encontrada: {$viewName}");
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}
