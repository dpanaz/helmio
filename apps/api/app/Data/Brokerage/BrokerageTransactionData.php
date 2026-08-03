<?php

namespace App\Data\Brokerage;

use Carbon\CarbonImmutable;

final readonly class BrokerageTransactionData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $providerTransactionId,
        public string $providerAccountId,
        public ?string $providerSecurityId,
        public string $transactionType,
        public CarbonImmutable $transactionDate,
        public ?CarbonImmutable $settlementDate,
        public ?float $quantity,
        public ?float $price,
        public float $grossAmount,
        public float $fees,
        public float $netAmount,
        public ?string $description,
        public array $metadata = [],
    ) {
    }
}