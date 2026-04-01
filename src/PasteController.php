<?php

declare(strict_types=1);

namespace App;

use Random\RandomException;

readonly class PasteController
{
    public function __construct(private PasteService $pasteService)
    {
    }

    public function find(string $token): void
    {
        $text = $this->pasteService->find($this->b64UrlToB64($token));
        if ($text === null) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        View::render('show', ['text' => $text]);
    }

    private function b64UrlToB64(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return strtr($input, '-_', '+/');
    }

    public function create(): void
    {
        View::render('create');
    }

    /**
     * @throws RandomException
     */
    public function store(): void
    {
        $text = trim($_POST['text'] ?? '');
        if ($text === '') {
            http_response_code(400);
            echo 'text is empty';
            return;
        }

        $token = $this->b64ToB64Url($this->pasteService->store($text));
        header("Location: /$token");
    }

    private function b64ToB64Url(string $input): string
    {
        return rtrim(strtr($input, '+/', '-_'), '=');
    }
}
