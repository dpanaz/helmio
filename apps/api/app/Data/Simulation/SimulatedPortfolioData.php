<?php

namespace App\Data\Simulation;

use Illuminate\Support\Collection;

final readonly class SimulatedPortfolioData
{
    /**
     * @param Collection<int, SimulatedHoldingData> $holdings
     */
    public function __construct(
        public Collection $holdings,
        public float $cash,
        public ?float $advisoryFeeRate = null,
    ) {
    }

    public function holdingsValue(): float
    {
        return (float) $this->holdings->sum(
            fn (SimulatedHoldingData $holding) =>
                $holding->marketValue
        );
    }

    public function totalValue(): float
    {
        return $this->holdingsValue() + $this->cash;
    }

    public function findHolding(string $symbol): ?SimulatedHoldingData
    {
        $symbol = strtoupper(trim($symbol));

        return $this->holdings->first(
            fn (SimulatedHoldingData $holding) =>
                strtoupper($holding->symbol) === $symbol
        );
    }
}