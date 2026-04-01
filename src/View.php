<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

class View
{
    private static string $templatePath = __DIR__ . '/../templates/';

    public static function render(string $template, array $data = []): void
    {
        extract($data);
        $file = self::$templatePath . $template . '.php';

        if (!file_exists($file)) {
            throw new RuntimeException("Template not found: $template");
        }

        require $file;
    }
}
