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
        private readonly string $bearerToken = '',
        private readonly int $maxRetries = 3,
        private readonly int $retryDelayMs = 250,
    ) {
    }

    /** @return array<int, Product> */
    public function getProducts(): array
    {
        $data = $this->requestFirstAvailable('GET', $this->expandUriVariants('product', 'products'));
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
        $data = $this->requestFirstAvailable(
            'GET',
            $this->expandUriVariants('product/' . $id, 'products/' . $id),
            allow404: true,
        );
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
        $data = $this->requestFirstAvailable('GET', $this->expandUriVariants('manufacturer', 'manufacturers'));
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
    private function requestFirstAvailable(string $method, array $uris, bool $allow404 = false): mixed
    {
        $lastException = null;
        foreach ($uris as $uri) {
            try {
                return $this->requestJson($method, $uri, $allow404);
            } catch (YsellApiException $exception) {
                $lastException = $exception;
                if ($exception->httpStatus() === 404) {
                    continue;
                }
                throw $exception;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        return [];
    }

    /** @return array<int, string> */
    private function expandUriVariants(string ...$bases): array
    {
        $result = [];
        foreach ($bases as $base) {
            $normalized = trim($base, '/');
            $variants = [
                $normalized,
                $normalized . '/',
                '/' . $normalized,
                '/' . $normalized . '/',
            ];

            foreach ($variants as $variant) {
                if (!in_array($variant, $result, true)) {
                    $result[] = $variant;
                }
            }
        }

        return $result;
    }

    /** @return mixed */
    private function requestJson(string $method, string $uri, bool $allow404 = false): mixed
    {
        $attempt = 0;
        $lastError = null;

        do {
            $attempt++;
            try {
                $options = [];
                if ($this->bearerToken !== '') {
                    $options['headers'] = ['Authorization' => 'Bearer ' . $this->bearerToken];
                }

                $response = $this->client->request($method, $uri, $options);
                $status = $response->getStatusCode();
                if ($allow404 && $status === 404) {
                    return [];
                }
                if ($status >= 400) {
                    $body = trim((string) $response->getBody());
                    $bodyPreview = mb_substr($body, 0, 300);
                    throw new YsellApiException(
                        sprintf('YSELL API HTTP %d for %s. Response: %s', $status, $uri, $bodyPreview),
                        httpStatus: $status,
                    );
                }

                return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException|GuzzleException $exception) {
                $lastError = sprintf('%s: %s', $exception::class, $exception->getMessage());
                if ($attempt >= $this->maxRetries) {
                    throw new YsellApiException(
                        sprintf(
                            'YSELL API request failed for %s after %d attempts. Last error: %s',
                            $uri,
                            $attempt,
                            $lastError,
                        ),
                        previous: $exception,
                    );
                }
                usleep($this->retryDelayMs * 1000 * $attempt);
            } catch (YsellApiException $exception) {
                throw $exception;
            }
        } while ($attempt < $this->maxRetries);

        if ($lastError !== null) {
            throw new YsellApiException(
                sprintf('YSELL API request failed for %s. Last error: %s', $uri, $lastError),
            );
        }

        return [];
    }
}
