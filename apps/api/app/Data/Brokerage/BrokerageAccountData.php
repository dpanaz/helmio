<?php

namespace App\Data\Brokerage;

final readonly class BrokerageAccountData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $providerAccountId,
        public string $name,
        public ?string $institutionName,
        public ?string $accountType,
        public ?string $accountNumberMask,
        public float $totalValue,
        public float $cashValue,
        public array $metadata = [],
    ) {
    }
}