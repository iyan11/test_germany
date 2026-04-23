<?php

declare(strict_types=1);

namespace App\Infrastructure\Api\Dto;

final readonly class ProductResponse
{
    public function __construct(
        public int $id,
        public string $extId,
        public string $title,
        public string $condition,
        public ?int $manufacturerId,
        public ?string $purchasePrice,
        public ?string $image,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            extId: (string) ($data['ext_id'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            condition: (string) ($data['condition'] ?? ''),
            manufacturerId: isset($data['manufacturer_id']) ? (int) $data['manufacturer_id'] : null,
            purchasePrice: isset($data['purchase_price']) ? (string) $data['purchase_price'] : null,
            image: isset($data['image']) ? (string) $data['image'] : null,
        );
    }
}
