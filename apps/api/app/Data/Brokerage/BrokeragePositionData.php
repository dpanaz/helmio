<?php

namespace App\Data\Brokerage;

final readonly class BrokeragePositionData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $providerPositionId,
        public ?string $providerSecurityId,
        public ?string $symbol,
        public string $name,
        public ?string $securityType,
        public ?string $assetClass,
        public ?string $sector,
        public float $quantity,
        public ?float $price,
        public float $marketValue,
        public ?float $costBasis,
        public ?float $expenseRatio,
        public array $metadata = [],
    ) {
    }
}