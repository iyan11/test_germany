<?php

declare(strict_types=1);

namespace App\Domain\Ebay;

use App\Domain\Ebay\ValueObject\Price;
use App\Domain\Ebay\ValueObject\Sku;
use App\Domain\Ebay\ValueObject\Title;
use App\Domain\Extraction\ExtractedMetadata;
use App\Domain\Product\Product;

final class EbayRowFactory
{
    /** @param array<string, string> $profiles */
    public function create(
        Product $product,
        string $brand,
        ExtractedMetadata $metadata,
        string $categoryId,
        string $description,
        float $price,
        array $profiles,
        string $location,
        string $formattedTitle,
    ): EbayRow {
        $title = new Title($formattedTitle);
        $priceVo = new Price($price);
        $isVioks = mb_strtolower($brand) === 'vioks';
        $skuValue = $isVioks ? $product->id . '-MP' : 'sku=' . $product->id;
        $sku = new Sku($skuValue);

        $conditionNew = mb_strtolower($product->condition) === 'new';
        $manufacturerNumber = $isVioks ? $sku->value : ($metadata->primaryModelNumber() ?? '');
        $compatibleBrands = $metadata->compatibleBrands !== [] ? $metadata->compatibleBrands : [$brand];

        return new EbayRow([
            '*Action(SiteID=Germany|Country=DE|Currency=EUR|Version=941)' => 'Add',
            '*Category' => $categoryId,
            '*Title' => $title->value,
            '*Description' => $description,
            '*ConditionID' => $conditionNew ? '1000' : '3000',
            'PicURL' => $product->image ?? '',
            '*Quantity' => '1',
            '*Format' => 'FixedPrice',
            '*StartPrice' => $priceVo->asString(),
            '*Duration' => 'GTC',
            '*Location' => $location,
            'ShippingProfileName' => $profiles['shipping_profile_name'],
            'ReturnProfileName' => $profiles['return_profile_name'],
            'PaymentProfileName' => $profiles['payment_profile_name'],
            'C:Marke' => $brand,
            'C:Produktart' => $metadata->productType,
            'Custom label (SKU)' => $sku->value,
            'ConditionDescription' => $conditionNew ? 'Neu' : 'Gebraucht',
            'C:Herstellernummer' => $manufacturerNumber,
            'C:Produkt' => $metadata->productType,
            'C:Modellkompatibilität' => implode(', ', $metadata->compatibleModelNumbers(3)),
            'C:Farbe' => $metadata->color ?? '',
            'C:Hersteller' => $brand,
            'C:Material' => $metadata->material ?? '',
            'C:Markenkompatibilität' => implode(', ', $compatibleBrands),
            'C: Installationsart' => 'Vollintegriert',
        ]);
    }
}
