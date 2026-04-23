<?php

declare(strict_types=1);

namespace App\Domain\Ebay\ValueObject;

use InvalidArgumentException;

final readonly class Sku
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }
    }
}
