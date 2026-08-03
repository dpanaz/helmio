<?php

namespace App\Services\Brokerage;

use App\Contracts\Brokerage\BrokerageProviderInterface;
use InvalidArgumentException;

class BrokerageProviderManager
{
    /**
     * @var array<string, BrokerageProviderInterface>
     */
    private array $providers = [];

    public function register(
        BrokerageProviderInterface $provider,
    ): void {
        $this->providers[
            $provider->providerName()
        ] = $provider;
    }

    public function driver(
        string $provider,
    ): BrokerageProviderInterface {
        if (! isset($this->providers[$provider])) {
            throw new InvalidArgumentException(
                "Brokerage provider [{$provider}] is not registered.",
            );
        }

        return $this->providers[$provider];
    }

    /**
     * @return array<int, string>
     */
    public function availableProviders(): array
    {
        return array_keys($this->providers);
    }
}