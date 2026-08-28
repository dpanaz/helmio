<?php

namespace App\Services\Simulation;

use App\Data\Simulation\PortfolioChangeData;
use App\Data\Simulation\SimulatedHoldingData;
use App\Data\Simulation\SimulatedPortfolioData;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PortfolioScenarioBuilder
{
    /**
     * @param array<int, PortfolioChangeData> $changes
     */
    public function apply(
        SimulatedPortfolioData $portfolio,
        array $changes,
    ): SimulatedPortfolioData {
        $result = $portfolio;

        foreach ($changes as $change) {
            $result = $this->applyChange(
                $result,
                $change,
            );
        }

        return $result;
    }

    public function applyChange(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        return match ($change->action) {
            PortfolioChangeData::ACTION_BUY =>
                $this->buy($portfolio, $change),

            PortfolioChangeData::ACTION_SELL =>
                $this->sell($portfolio, $change),

            PortfolioChangeData::ACTION_REMOVE =>
                $this->remove($portfolio, $change),

            PortfolioChangeData::ACTION_SET_VALUE =>
                $this->setValue($portfolio, $change),

            PortfolioChangeData::ACTION_CHANGE_ADVISORY_FEE =>
                $this->changeAdvisoryFee(
                    $portfolio,
                    $change,
                ),

            default => throw new InvalidArgumentException(
                'Unknown simulation change.'
            ),
        };
    }

    private function buy(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        $amount = max(0, (float) $change->amount);

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Buy amount must be greater than zero.'
            );
        }

        if ($amount > $portfolio->cash) {
            throw new InvalidArgumentException(
                'Hypothetical buy exceeds available cash.'
            );
        }

        $symbol = strtoupper(trim(
            $change->symbol
                ?? $change->security?->symbol
                ?? ''
        ));

        if ($symbol === '') {
            throw new InvalidArgumentException(
                'A symbol is required for a hypothetical buy.'
            );
        }

        $holdings = $this->cloneHoldings(
            $portfolio->holdings
        );

        $existingIndex = $holdings->search(
            fn (SimulatedHoldingData $holding) =>
                strtoupper($holding->symbol) === $symbol
        );

        if ($existingIndex !== false) {
            /** @var SimulatedHoldingData $existing */
            $existing = $holdings->get($existingIndex);

            $holdings->put(
                $existingIndex,
                $existing->withMarketValue(
                    $existing->marketValue + $amount
                )
            );
        } else {
            if (! $change->security) {
                throw new InvalidArgumentException(
                    "Security details are required to add {$symbol}."
                );
            }

            $security = $change->security;

            $price = max(0, $security->price);

            $quantity = $price > 0
                ? $amount / $price
                : 0;

            $holdings->push(
                new SimulatedHoldingData(
                    securityId: $security->securityId,
                    symbol: $security->symbol,
                    name: $security->name,
                    quantity: $quantity,
                    price: $security->price,
                    marketValue: $amount,
                    assetClass: $security->assetClass,
                    sector: $security->sector,
                    expenseRatio: $security->expenseRatio,
                    costBasis: $security->costBasis,
                    accountId: $security->accountId,
                    accountType: $security->accountType,
                )
            );
        }

        return new SimulatedPortfolioData(
            holdings: $holdings->values(),
            cash: $portfolio->cash - $amount,
            advisoryFeeRate: $portfolio->advisoryFeeRate,
        );
    }

    private function sell(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        $symbol = strtoupper(trim(
            (string) $change->symbol
        ));

        $holdings = $this->cloneHoldings(
            $portfolio->holdings
        );

        $index = $holdings->search(
            fn (SimulatedHoldingData $holding) =>
                strtoupper($holding->symbol) === $symbol
        );

        if ($index === false) {
            throw new InvalidArgumentException(
                "Holding {$symbol} was not found."
            );
        }

        /** @var SimulatedHoldingData $holding */
        $holding = $holdings->get($index);

        $sellAmount = $this->resolveSellAmount(
            $holding,
            $change,
        );

        if ($sellAmount <= 0) {
            throw new InvalidArgumentException(
                'Sell amount must be greater than zero.'
            );
        }

        if ($sellAmount > $holding->marketValue) {
            $sellAmount = $holding->marketValue;
        }

        $remainingValue =
            $holding->marketValue - $sellAmount;

        if ($remainingValue <= 0.01) {
            $holdings->forget($index);
        } else {
            $holdings->put(
                $index,
                $holding->withMarketValue(
                    $remainingValue
                )
            );
        }

        return new SimulatedPortfolioData(
            holdings: $holdings->values(),
            cash: $portfolio->cash + $sellAmount,
            advisoryFeeRate: $portfolio->advisoryFeeRate,
        );
    }

    private function remove(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        $symbol = strtoupper(trim(
            (string) $change->symbol
        ));

        $holding = $portfolio->findHolding($symbol);

        if (! $holding) {
            throw new InvalidArgumentException(
                "Holding {$symbol} was not found."
            );
        }

        return $this->sell(
            $portfolio,
            new PortfolioChangeData(
                action: PortfolioChangeData::ACTION_SELL,
                symbol: $symbol,
                amount: $holding->marketValue,
            )
        );
    }

    private function setValue(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        $symbol = strtoupper(trim(
            (string) $change->symbol
        ));

        $targetValue = max(
            0,
            (float) $change->amount
        );

        $holding = $portfolio->findHolding($symbol);

        if (! $holding) {
            throw new InvalidArgumentException(
                "Holding {$symbol} was not found."
            );
        }

        $difference =
            $targetValue - $holding->marketValue;

        if ($difference > 0) {
            return $this->buy(
                $portfolio,
                new PortfolioChangeData(
                    action: PortfolioChangeData::ACTION_BUY,
                    symbol: $symbol,
                    amount: $difference,
                )
            );
        }

        if ($difference < 0) {
            return $this->sell(
                $portfolio,
                new PortfolioChangeData(
                    action: PortfolioChangeData::ACTION_SELL,
                    symbol: $symbol,
                    amount: abs($difference),
                )
            );
        }

        return $portfolio;
    }

    private function changeAdvisoryFee(
        SimulatedPortfolioData $portfolio,
        PortfolioChangeData $change,
    ): SimulatedPortfolioData {
        $rate = $change->advisoryFeeRate;

        if ($rate === null || $rate < 0) {
            throw new InvalidArgumentException(
                'A valid advisory fee rate is required.'
            );
        }

        return new SimulatedPortfolioData(
            holdings: $this->cloneHoldings(
                $portfolio->holdings
            ),
            cash: $portfolio->cash,
            advisoryFeeRate: $rate,
        );
    }

    private function resolveSellAmount(
        SimulatedHoldingData $holding,
        PortfolioChangeData $change,
    ): float {
        if ($change->percentage !== null) {
            $percentage = min(
                1,
                max(0, $change->percentage)
            );

            return
                $holding->marketValue
                * $percentage;
        }

        return max(
            0,
            (float) $change->amount
        );
    }

    /**
     * @param Collection<int, SimulatedHoldingData> $holdings
     *
     * @return Collection<int, SimulatedHoldingData>
     */
    private function cloneHoldings(
        Collection $holdings,
    ): Collection {
        return $holdings
            ->map(
                fn (SimulatedHoldingData $holding) =>
                    new SimulatedHoldingData(
                        securityId: $holding->securityId,
                        symbol: $holding->symbol,
                        name: $holding->name,
                        quantity: $holding->quantity,
                        price: $holding->price,
                        marketValue: $holding->marketValue,
                        assetClass: $holding->assetClass,
                        sector: $holding->sector,
                        expenseRatio: $holding->expenseRatio,
                        costBasis: $holding->costBasis,
                        accountId: $holding->accountId,
                        accountType: $holding->accountType,
                    )
            )
            ->values();
    }
}