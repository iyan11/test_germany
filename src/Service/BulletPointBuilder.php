<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

final class BulletPointBuilder
{
    /** @return array<int, string> */
    public function build(Product $product, string $brand, ExtractedMetadata $metadata): array
    {
        $condition = mb_strtolower($product->condition) === 'new' ? 'Neuware' : 'Gebrauchtteil';
        $device = $metadata->deviceCategoryHint ?? 'Haushaltsgerät';
        $modelHint = $metadata->primaryModelNumber() ?? 'ohne spezifische Modellnummer';

        return [
            sprintf('Präzise Passform als %s für %s.', $metadata->productType, $device),
            sprintf('Marke/Kompatibilität: %s.', $brand),
            sprintf('Zustand: %s.', $condition),
            sprintf('Referenznummer: %s.', $modelHint),
            'Schneller Einbau bei fachgerechter Montage möglich.',
        ];
    }
}
