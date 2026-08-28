<?php

namespace App\Data\Simulation;

final readonly class SimulatedHoldingData
{
    public function __construct(
        public ?int $securityId,
        public string $symbol,
        public string $name,
        public float $quantity,
        public float $price,
        public float $marketValue,
        public ?string $assetClass = null,
        public ?string $sector = null,
        public ?float $expenseRatio = null,
        public ?float $costBasis = null,
        public ?int $accountId = null,
        public ?string $accountType = null,
    ) {
    }

    public function weight(float $portfolioValue): float
    {
        if ($portfolioValue <= 0) {
            return 0.0;
        }

        return $this->marketValue / $portfolioValue;
    }

    public function withMarketValue(float $marketValue): self
    {
        $quantity = $this->price > 0
            ? $marketValue / $this->price
            : $this->quantity;

        return new self(
            securityId: $this->securityId,
            symbol: $this->symbol,
            name: $this->name,
            quantity: $quantity,
            price: $this->price,
            marketValue: max(0, $marketValue),
            assetClass: $this->assetClass,
            sector: $this->sector,
            expenseRatio: $this->expenseRatio,
            costBasis: $this->costBasis,
            accountId: $this->accountId,
            accountType: $this->accountType,
        );
    }
}