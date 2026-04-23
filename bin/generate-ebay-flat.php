#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Application\Command\GenerateEbayFlatCommand;
use App\Application\Service\GenerateEbayFlatService;
use App\Domain\Ebay\EbayRowFactory;
use App\Infrastructure\Api\YsellClient;
use App\Infrastructure\Export\CsvExporter;
use App\Infrastructure\Export\XlsxTemplateExporter;
use App\Infrastructure\Logging\ConsoleLogger;
use App\Infrastructure\Template\HtmlTemplateRenderer;
use App\Service\BrandCompatibilityExtractor;
use App\Service\BulletPointBuilder;
use App\Service\ColorExtractor;
use App\Service\DescriptionBuilder;
use App\Service\DeviceCategoryHintExtractor;
use App\Service\HeuristicPriceCalculator;
use App\Service\LocalKeywordCategoryResolver;
use App\Service\MaterialExtractor;
use App\Service\ModelNumberExtractor;
use App\Service\ProductMetadataExtractor;
use App\Service\ProductTypeExtractor;
use App\Service\TitleFormatter;
use App\Validator\EbayRowValidator;
use Dotenv\Dotenv;
use GuzzleHttp\Client;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
Dotenv::createImmutable($root)->safeLoad();

$appConfig = require $root . '/config/app.php';
$categoryMap = require $root . '/config/categories.php';

$options = getopt('', ['template:', 'output:', 'limit::', 'product-id::', 'dry-run', 'verbose', 'format::']);
$template = $options['template'] ?? null;
$output = $options['output'] ?? null;
$limit = isset($options['limit']) ? (int) $options['limit'] : null;
$productId = isset($options['product-id']) ? (int) $options['product-id'] : null;
$dryRun = array_key_exists('dry-run', $options);
$verbose = array_key_exists('verbose', $options);
$format = strtolower((string) ($options['format'] ?? 'xlsx'));

if (!is_string($template) || !is_string($output)) {
    fwrite(STDERR, "Required args: --template=PATH --output=PATH\n");
    exit(1);
}
if (!in_array($format, ['xlsx', 'csv'], true)) {
    fwrite(STDERR, "--format must be xlsx or csv\n");
    exit(1);
}

$logger = new ConsoleLogger($verbose);

$baseUrl = rtrim((string) $appConfig['ysell']['base_url'], '/');
if ($baseUrl === '') {
    fwrite(
        STDERR,
        "YSELL_BASE_URL is empty. Set it in .env (example: https://4457.test1.ysell.pro/api)\n",
    );
    exit(1);
}

$httpClient = new Client([
    'base_uri' => $baseUrl . '/',
    'timeout' => $appConfig['ysell']['timeout'],
    'http_errors' => false,
]);

$service = new GenerateEbayFlatService(
    ysellClient: new YsellClient(
        client: $httpClient,
        bearerToken: (string) $appConfig['ysell']['bearer_token'],
    ),
    categoryResolver: new LocalKeywordCategoryResolver($categoryMap, $appConfig['ebay']['fallback_category_id']),
    priceCalculator: new HeuristicPriceCalculator(
        markup: (float) $appConfig['pricing']['markup'],
        defaultPrice: (float) $appConfig['pricing']['default_price'],
        defaultMinPrice: (float) $appConfig['pricing']['default_min_price'],
    ),
    metadataExtractor: new ProductMetadataExtractor(
        new ProductTypeExtractor(),
        new MaterialExtractor(),
        new ColorExtractor(),
        new DeviceCategoryHintExtractor(),
        new ModelNumberExtractor(),
        new BrandCompatibilityExtractor(),
    ),
    descriptionBuilder: new DescriptionBuilder(
        new HtmlTemplateRenderer(),
        new BulletPointBuilder(),
        (string) $appConfig['description_template_path'],
    ),
    titleFormatter: new TitleFormatter(),
    ebayRowFactory: new EbayRowFactory(),
    validator: new EbayRowValidator(),
    xlsxExporter: new XlsxTemplateExporter(),
    csvExporter: new CsvExporter(),
    logger: $logger,
    ebayConfig: $appConfig['ebay'],
);

$command = new GenerateEbayFlatCommand(
    template: $template,
    output: $output,
    limit: $limit,
    productId: $productId,
    dryRun: $dryRun,
    verbose: $verbose,
    format: $format,
);

try {
    $service->handle($command);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Generation failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
