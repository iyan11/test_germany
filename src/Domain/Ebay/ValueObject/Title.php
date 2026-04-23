<?php

declare(strict_types=1);

namespace App\Domain\Ebay\ValueObject;

use InvalidArgumentException;

final readonly class Title
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('Title cannot be empty.');
        }
        if (mb_strlen($value) > 80) {
            throw new InvalidArgumentException('Title cannot be longer than 80 chars.');
        }
    }
}
