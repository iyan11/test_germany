<?php

declare(strict_types=1);

namespace App\Domain\Ebay;

final class EbayRow
{
    /** @param array<string, string> $values */
    public function __construct(private array $values)
    {
    }

    /** @return array<string, string> */
    public function values(): array
    {
        return $this->values;
    }
}
