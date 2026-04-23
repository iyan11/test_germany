<?php

declare(strict_types=1);

namespace App\Application\Command;

final readonly class GenerateEbayFlatCommand
{
    public function __construct(
        public string $template,
        public string $output,
        public ?int $limit,
        public ?int $productId,
        public bool $dryRun,
        public bool $verbose,
        public string $format,
    ) {
    }
}
