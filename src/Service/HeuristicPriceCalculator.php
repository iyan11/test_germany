<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\PriceCalculatorInterface;
use App\Domain\Product\Product;

final class HeuristicPriceCalculator implements PriceCalculatorInterface
{
    public function __construct(
        private readonly float $markup,
        private readonly float $defaultPrice,
        private readonly float $defaultMinPrice,
    ) {
    }

    public function calculate(Product $product): float
    {
        if ($product->purchasePrice === null || $product->purchasePrice <= 0) {
            return $this->defaultPrice;
        }

        $raw = $product->purchasePrice * $this->markup;
        $rounded = floor($raw) + 0.99;

        return max($rounded, $this->defaultMinPrice);
    }
}
