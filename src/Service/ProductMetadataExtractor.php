<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

final class ProductMetadataExtractor
{
    public function __construct(
        private readonly ProductTypeExtractor $productTypeExtractor,
        private readonly MaterialExtractor $materialExtractor,
        private readonly ColorExtractor $colorExtractor,
        private readonly DeviceCategoryHintExtractor $deviceCategoryHintExtractor,
        private readonly ModelNumberExtractor $modelNumberExtractor,
        private readonly BrandCompatibilityExtractor $brandCompatibilityExtractor,
    ) {
    }

    public function extract(Product $product): ExtractedMetadata
    {
        return new ExtractedMetadata(
            productType: $this->productTypeExtractor->extract($product->title),
            material: $this->materialExtractor->extract($product->title),
            color: $this->colorExtractor->extract($product->title),
            deviceCategoryHint: $this->deviceCategoryHintExtractor->extract($product->title),
            modelNumbers: $this->modelNumberExtractor->extractAll($product->title),
            compatibleBrands: $this->brandCompatibilityExtractor->extract($product->title),
        );
    }
}
