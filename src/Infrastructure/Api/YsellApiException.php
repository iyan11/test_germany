<?php

declare(strict_types=1);

namespace App\Infrastructure\Api;

use RuntimeException;

final class YsellApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $httpStatus = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus ?? 0, $previous);
    }

    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }
}
