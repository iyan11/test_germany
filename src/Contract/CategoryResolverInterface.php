<?php

declare(strict_types=1);

namespace App\Contract;

use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

interface CategoryResolverInterface
{
    /** @return array{categoryId:string,isFallback:bool} */
    public function resolve(Product $product, ExtractedMetadata $metadata): array;
}
