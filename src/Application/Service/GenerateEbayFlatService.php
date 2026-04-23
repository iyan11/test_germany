<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Command\GenerateEbayFlatCommand;
use App\Contract\CategoryResolverInterface;
use App\Contract\PriceCalculatorInterface;
use App\Domain\Ebay\EbayRow;
use App\Domain\Ebay\EbayRowFactory;
use App\Domain\Product\Manufacturer;
use App\Domain\Product\Product;
use App\Infrastructure\Api\YsellClient;
use App\Infrastructure\Export\CsvExporter;
use App\Infrastructure\Export\XlsxTemplateExporter;
use App\Infrastructure\Logging\ConsoleLogger;
use App\Service\DescriptionBuilder;
use App\Service\ProductMetadataExtractor;
use App\Service\TitleFormatter;
use App\Validator\EbayRowValidator;

final class GenerateEbayFlatService
{
    /** @param array<string, mixed> $ebayConfig */
    public function __construct(
        private readonly YsellClient $ysellClient,
        private readonly CategoryResolverInterface $categoryResolver,
        private readonly PriceCalculatorInterface $priceCalculator,
        private readonly ProductMetadataExtractor $metadataExtractor,
        private readonly DescriptionBuilder $descriptionBuilder,
        private readonly TitleFormatter $titleFormatter,
        private readonly EbayRowFactory $ebayRowFactory,
        private readonly EbayRowValidator $validator,
        private readonly XlsxTemplateExporter $xlsxExporter,
        private readonly CsvExporter $csvExporter,
        private readonly ConsoleLogger $logger,
        private readonly array $ebayConfig,
    ) {
    }

    public function handle(GenerateEbayFlatCommand $command): void
    {
        $this->logger->info('Generation started.');
        $this->logger->info(sprintf('Mode: format=%s dryRun=%s', $command->format, $command->dryRun ? 'yes' : 'no'));

        $manufacturers = $this->ysellClient->getManufacturers();
        $manufacturerMap = $this->indexManufacturers($manufacturers);

        $products = $command->productId !== null
            ? array_filter([$this->ysellClient->getProductById($command->productId)])
            : $this->ysellClient->getProducts();

        if ($command->limit !== null) {
            $products = array_slice($products, 0, $command->limit);
        }

        $this->logger->info(sprintf('Fetched products=%d manufacturers=%d', count($products), count($manufacturers)));

        $rows = [];
        $processed = $written = $skipped = $warnings = 0;

        foreach ($products as $product) {
            $processed++;
            $this->logger->debug(sprintf('Processing product #%d: %s', $product->id, $product->title));

            try {
                $brand = $this->resolveBrand($product, $manufacturerMap);
                $metadata = $this->metadataExtractor->extract($product);
                $category = $this->categoryResolver->resolve($product, $metadata);
                if ($category['isFallback']) {
                    $warnings++;
                    $this->logger->warning(sprintf('Fallback category used for product #%d', $product->id));
                }

                if (($product->image ?? '') === '') {
                    $warnings++;
                    $this->logger->warning(sprintf('No image for product #%d', $product->id));
                }

                $price = $this->priceCalculator->calculate($product);
                $warnings++;
                $this->logger->warning(sprintf('Heuristic price %.2f for product #%d', $price, $product->id));

                $description = $this->descriptionBuilder->build($product, $brand, $metadata);
                $formattedTitle = $this->titleFormatter->format($product->title);

                $row = $this->ebayRowFactory->create(
                    product: $product,
                    brand: $brand,
                    metadata: $metadata,
                    categoryId: $category['categoryId'],
                    description: $description,
                    price: $price,
                    profiles: [
                        'shipping_profile_name' => (string) $this->ebayConfig['shipping_profile_name'],
                        'return_profile_name' => (string) $this->ebayConfig['return_profile_name'],
                        'payment_profile_name' => (string) $this->ebayConfig['payment_profile_name'],
                    ],
                    location: (string) $this->ebayConfig['location'],
                    formattedTitle: $formattedTitle,
                );

                $errors = $this->validator->validate($row);
                if ($errors !== []) {
                    $skipped++;
                    $this->logger->error(sprintf('Product #%d skipped: %s', $product->id, implode('; ', $errors)));
                    continue;
                }

                $rows[] = $row;
                $written++;
                $this->logger->debug(sprintf('Row prepared for product #%d', $product->id));
            } catch (\Throwable $e) {
                $skipped++;
                $this->logger->error(sprintf('Product #%d skipped due to error: %s', $product->id, $e->getMessage()));
            }
        }

        if (!$command->dryRun) {
            $this->save($command, $rows);
        }

        $this->logger->info(sprintf('Done. processed=%d written=%d skipped=%d warnings=%d', $processed, $written, $skipped, $warnings));
    }

    /** @param array<int, Manufacturer> $manufacturers @return array<int, Manufacturer> */
    private function indexManufacturers(array $manufacturers): array
    {
        $map = [];
        foreach ($manufacturers as $manufacturer) {
            $map[$manufacturer->id] = $manufacturer;
        }

        return $map;
    }

    /** @param array<int, Manufacturer> $manufacturerMap */
    private function resolveBrand(Product $product, array $manufacturerMap): string
    {
        if ($product->manufacturerId !== null && isset($manufacturerMap[$product->manufacturerId])) {
            return $manufacturerMap[$product->manufacturerId]->name;
        }

        return 'Unknown';
    }

    /** @param array<int, EbayRow> $rows */
    private function save(GenerateEbayFlatCommand $command, array $rows): void
    {
        if ($command->format === 'csv') {
            $this->csvExporter->export($command->template, $command->output, $rows);
            $this->logger->info('CSV exported: ' . $command->output);
            return;
        }

        $this->xlsxExporter->export($command->template, $command->output, $rows);
        $this->logger->info('XLSX exported: ' . $command->output);
    }
}
