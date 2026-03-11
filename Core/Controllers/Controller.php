<?php

namespace Core\Controllers;

use Core\Exceptions\HttpException;

abstract class Controller
{
    protected function requireMethod(string ...$allowedMethods): void
    {
        $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? ''));
        $allowed = array_map(static fn(string $method): string => strtoupper($method), $allowedMethods);

        if (!in_array($requestMethod, $allowed, true)) {
            $this->abort(405, 'Method Not Allowed');
        }
    }

    protected function requirePositiveIdFromQuery(string $field = 'id', string $message = 'Invalid ID'): int
    {
        $id = filter_input(INPUT_GET, $field, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            $this->abort(400, $message);
        }

        return $id;
    }

    protected function requirePositiveIdFromPost(string $field = 'id', string $missingMessage = 'Missing ID', string $invalidMessage = 'Invalid ID'): int
    {
        if (!isset($_POST[$field])) {
            $this->abort(400, $missingMessage);
        }

        $id = (int) $_POST[$field];
        if ($id <= 0) {
            $this->abort(400, $invalidMessage);
        }

        return $id;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function abort(int $statusCode, string $message): void
    {
        throw new HttpException($statusCode, $message);
    }
}
