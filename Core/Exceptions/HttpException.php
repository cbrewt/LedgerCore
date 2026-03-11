<?php

namespace Core\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    private int $statusCode;
    private string $userMessage;

    public function __construct(int $statusCode, string $userMessage, ?\Throwable $previous = null)
    {
        parent::__construct($userMessage, 0, $previous);
        $this->statusCode = $statusCode;
        $this->userMessage = $userMessage;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function userMessage(): string
    {
        return $this->userMessage;
    }
}

