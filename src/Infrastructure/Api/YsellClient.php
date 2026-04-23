<?php

declare(strict_types=1);

namespace App\Infrastructure\Api;

use App\Domain\Product\Manufacturer;
use App\Domain\Product\Product;
use App\Infrastructure\Api\Dto\ManufacturerResponse;
use App\Infrastructure\Api\Dto\ProductResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

final class YsellClient
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly int $maxRetries = 3,
        private readonly int $retryDelayMs = 250,
    ) {
    }

    /** @return array<int, Product> */
    public function getProducts(): array
    {
        $data = $this->requestJson('GET', '/product');
        if (!is_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $dto = ProductResponse::fromArray($item);
            $result[] = new Product(
                id: $dto->id,
                extId: $dto->extId,
                title: $dto->title,
                condition: $dto->condition,
                manufacturerId: $dto->manufacturerId,
                purchasePrice: $dto->purchasePrice !== null ? (float) $dto->purchasePrice : null,
                image: $dto->image,
            );
        }

        return $result;
    }

    public function getProductById(int $id): ?Product
    {
        $data = $this->requestJson('GET', '/product/' . $id, allow404: true);
        if (!is_array($data) || $data === []) {
            return null;
        }

        $dto = ProductResponse::fromArray($data);

        return new Product(
            id: $dto->id,
            extId: $dto->extId,
            title: $dto->title,
            condition: $dto->condition,
            manufacturerId: $dto->manufacturerId,
            purchasePrice: $dto->purchasePrice !== null ? (float) $dto->purchasePrice : null,
            image: $dto->image,
        );
    }

    /** @return array<int, Manufacturer> */
    public function getManufacturers(): array
    {
        $data = $this->requestJson('GET', '/manufacturer');
        if (!is_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $dto = ManufacturerResponse::fromArray($item);
            $result[] = new Manufacturer($dto->id, $dto->name);
        }

        return $result;
    }

    /** @return mixed */
    private function requestJson(string $method, string $uri, bool $allow404 = false): mixed
    {
        $attempt = 0;
        do {
            $attempt++;
            try {
                $response = $this->client->request($method, $uri);
                $status = $response->getStatusCode();
                if ($allow404 && $status === 404) {
                    return [];
                }
                if ($status >= 400) {
                    throw new YsellApiException(sprintf('YSELL API HTTP %d for %s', $status, $uri));
                }

                return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException|GuzzleException $exception) {
                if ($attempt >= $this->maxRetries) {
                    throw new YsellApiException(
                        sprintf('YSELL API request failed for %s after %d attempts', $uri, $attempt),
                        previous: $exception,
                    );
                }
                usleep($this->retryDelayMs * 1000 * $attempt);
            }
        } while ($attempt < $this->maxRetries);

        return [];
    }
}
