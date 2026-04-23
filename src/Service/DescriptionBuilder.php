<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\TemplateRendererInterface;
use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

final class DescriptionBuilder
{
    public function __construct(
        private readonly TemplateRendererInterface $renderer,
        private readonly BulletPointBuilder $bulletPointBuilder,
        private readonly string $templatePath,
    ) {
    }

    public function build(Product $product, string $brand, ExtractedMetadata $metadata): string
    {
        $bullets = $this->bulletPointBuilder->build($product, $brand, $metadata);
        $descriptionText = sprintf(
            'Dieses %s ist passend bzw. kompatibel für ausgewählte Geräte der Kategorie %s. ' .
            'Die Ausführung ist auf eine zuverlässige Funktion im täglichen Einsatz ausgelegt.',
            $metadata->productType,
            $metadata->deviceCategoryHint ?? 'Haushaltsgeräte',
        );

        return $this->renderer->render($this->templatePath, [
            'TITLE' => $product->title,
            'IMAGE_URL' => $product->image ?? '',
            'BULLET_1' => $bullets[0] ?? '',
            'BULLET_2' => $bullets[1] ?? '',
            'BULLET_3' => $bullets[2] ?? '',
            'BULLET_4' => $bullets[3] ?? '',
            'BULLET_5' => $bullets[4] ?? '',
            'DESCRIPTION_TEXT' => $descriptionText,
            'PASSEND_FUER' => $metadata->deviceCategoryHint ?? 'Diverse Modelle',
            'KOMPATIBEL_MIT' => implode(', ', $metadata->compatibleBrands),
        ]);
    }
}
