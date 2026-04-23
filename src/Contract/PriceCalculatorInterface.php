<?php

declare(strict_types=1);

namespace App\Contract;

use App\Domain\Product\Product;

interface PriceCalculatorInterface
{
    public function calculate(Product $product): float;
}
