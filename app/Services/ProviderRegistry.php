<?php

namespace App\Services;

use App\Services\Interfaces\ProviderRawStoreInterface;
use InvalidArgumentException;

class ProviderRegistry
{
    /** @var array<string, ProviderRawStoreInterface> */
    private array $rawStores = [];

    public function __construct(iterable $rawStores)
    {
        foreach ($rawStores as $store) {
            $this->rawStores[$store->provider()] = $store;
        }
    }

    public function rawStore(string $provider): ProviderRawStoreInterface
    {
        return $this->rawStores[$provider]
            ?? throw new InvalidArgumentException("Unknown provider [{$provider}]");
    }
}
