<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\CategoryResolverInterface;
use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

final class LocalKeywordCategoryResolver implements CategoryResolverInterface
{
    /** @param array<string, string> $keywordMap */
    public function __construct(
        private readonly array $keywordMap,
        private readonly string $fallbackCategory,
    ) {
    }

    public function resolve(Product $product, ExtractedMetadata $metadata): array
    {
        $title = mb_strtolower($product->title);
        foreach ($this->keywordMap as $keyword => $categoryId) {
            if (mb_strpos($title, mb_strtolower($keyword)) !== false) {
                return ['categoryId' => $categoryId, 'isFallback' => false];
            }
        }

        return ['categoryId' => $this->fallbackCategory, 'isFallback' => true];
    }
}
