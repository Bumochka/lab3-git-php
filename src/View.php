<?php

namespace App;

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data);
        $contentFile = __DIR__ . '/../templates/' . $template . '.php';
        require __DIR__ . '/../templates/layout.php';
    }
}