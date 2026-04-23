<?php

declare(strict_types=1);

namespace App\Domain\Ebay\ValueObject;

use InvalidArgumentException;

final readonly class Price
{
    public function __construct(public float $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Price must be positive.');
        }
    }

    public function asString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
