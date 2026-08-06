<?php

namespace App\Services\Timeline;

use App\Enums\TimelineEventType;
use App\Models\PortfolioStateSnapshot;
use App\Models\PortfolioStateSnapshotHolding;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class HoldingChangeDetectionService
{
    /**
     * @return Collection<int, TimelineEvent>
     */
    public function detectLatest(
        User $user,
    ): Collection {
        $snapshots = PortfolioStateSnapshot::query()
            ->where('user_id', $user->id)
            ->with('holdings')
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $current = $snapshots->first();
        $previous = $snapshots->skip(1)->first();

        if ($current === null || $previous === null) {
            return collect();
        }

        return $this->compare(
            $user,
            $previous,
            $current,
        );
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    public function compare(
        User $user,
        PortfolioStateSnapshot $previous,
        PortfolioStateSnapshot $current,
    ): Collection {
        $previous->loadMissing('holdings');
        $current->loadMissing('holdings');

        $previousHoldings = $previous
            ->holdings
            ->keyBy('holding_key');

        $currentHoldings = $current
            ->holdings
            ->keyBy('holding_key');

        $events = collect();

        foreach ($currentHoldings as $key => $holding) {
            $priorHolding = $previousHoldings->get(
                $key,
            );

            if ($priorHolding === null) {
                $events->push(
                    $this->holdingAdded(
                        $user,
                        $current,
                        $holding,
                    ),
                );

                continue;
            }

            $weightEvent = $this->weightChange(
                $user,
                $current,
                $priorHolding,
                $holding,
            );

            if ($weightEvent !== null) {
                $events->push($weightEvent);
            }

            $valueEvent = $this->valueChange(
                $user,
                $current,
                $priorHolding,
                $holding,
            );

            if ($valueEvent !== null) {
                $events->push($valueEvent);
            }
        }

        foreach ($previousHoldings as $key => $holding) {
            if (! $currentHoldings->has($key)) {
                $events->push(
                    $this->holdingRemoved(
                        $user,
                        $current,
                        $holding,
                    ),
                );
            }
        }

        return $events->filter()->values();
    }

    private function holdingAdded(
        User $user,
        PortfolioStateSnapshot $snapshot,
        PortfolioStateSnapshotHolding $holding,
    ): TimelineEvent {
        return $this->storeEvent(
            user: $user,
            snapshot: $snapshot,
            holding: $holding,
            type: TimelineEventType::HoldingAdded,
            category: 'holdings',
            severity: 'information',
            headline: sprintf(
                '%s was added',
                $this->holdingLabel($holding),
            ),
            summary: sprintf(
                '%s appeared in the portfolio with a market value of $%s.',
                $this->holdingLabel($holding),
                number_format(
                    (float) $holding->market_value,
                    2,
                ),
            ),
            before: null,
            after: [
                'quantity' =>
                    (float) $holding->quantity,

                'market_value' =>
                    (float) $holding->market_value,

                'portfolio_weight' =>
                    $holding->portfolio_weight !== null
                        ? (float) $holding->portfolio_weight
                        : null,
            ],
            metrics: [
                'market_value' =>
                    (float) $holding->market_value,

                'portfolio_weight' =>
                    $holding->portfolio_weight !== null
                        ? (float) $holding->portfolio_weight
                        : null,
            ],
            suffix: 'added',
        );
    }

    private function holdingRemoved(
        User $user,
        PortfolioStateSnapshot $snapshot,
        PortfolioStateSnapshotHolding $holding,
    ): TimelineEvent {
        return $this->storeEvent(
            user: $user,
            snapshot: $snapshot,
            holding: $holding,
            type: TimelineEventType::HoldingRemoved,
            category: 'holdings',
            severity: 'information',
            headline: sprintf(
                '%s was removed',
                $this->holdingLabel($holding),
            ),
            summary: sprintf(
                '%s no longer appears in the current portfolio snapshot.',
                $this->holdingLabel($holding),
            ),
            before: [
                'quantity' =>
                    (float) $holding->quantity,

                'market_value' =>
                    (float) $holding->market_value,

                'portfolio_weight' =>
                    $holding->portfolio_weight !== null
                        ? (float) $holding->portfolio_weight
                        : null,
            ],
            after: null,
            metrics: [
                'previous_market_value' =>
                    (float) $holding->market_value,
            ],
            suffix: 'removed',
        );
    }

    private function weightChange(
        User $user,
        PortfolioStateSnapshot $snapshot,
        PortfolioStateSnapshotHolding $before,
        PortfolioStateSnapshotHolding $after,
    ): ?TimelineEvent {
        if (
            $before->portfolio_weight === null
            || $after->portfolio_weight === null
        ) {
            return null;
        }

        $previousWeight =
            (float) $before->portfolio_weight;

        $currentWeight =
            (float) $after->portfolio_weight;

        $change =
            $currentWeight - $previousWeight;

        /*
         * Ignore moves smaller than two percentage points.
         */
        if (abs($change) < 0.02) {
            return null;
        }

        $increased = $change > 0;

        return $this->storeEvent(
            user: $user,
            snapshot: $snapshot,
            holding: $after,
            type: $increased
                ? TimelineEventType::WeightIncrease
                : TimelineEventType::WeightDecrease,
            category: 'diversification',
            severity: $increased
                && $currentWeight >= 0.25
                    ? 'medium'
                    : 'information',
            headline: sprintf(
                '%s portfolio weight %s',
                $this->holdingLabel($after),
                $increased
                    ? 'increased'
                    : 'decreased',
            ),
            summary: sprintf(
                '%s changed from %s%% to %s%% of the portfolio.',
                $this->holdingLabel($after),
                number_format(
                    $previousWeight * 100,
                    1,
                ),
                number_format(
                    $currentWeight * 100,
                    1,
                ),
            ),
            before: [
                'portfolio_weight' =>
                    $previousWeight,
            ],
            after: [
                'portfolio_weight' =>
                    $currentWeight,
            ],
            metrics: [
                'weight_change' =>
                    round($change, 8),

                'percentage_point_change' =>
                    round($change * 100, 2),
            ],
            suffix: 'weight-change',
        );
    }

    private function valueChange(
        User $user,
        PortfolioStateSnapshot $snapshot,
        PortfolioStateSnapshotHolding $before,
        PortfolioStateSnapshotHolding $after,
    ): ?TimelineEvent {
        $previousValue =
            (float) $before->market_value;

        $currentValue =
            (float) $after->market_value;

        $change =
            $currentValue - $previousValue;

        if (abs($change) < 1000) {
            return null;
        }

        $percentageChange = $previousValue > 0
            ? $change / $previousValue
            : null;

        /*
         * Ignore changes below 5% unless the dollar change is large.
         */
        if (
            abs($change) < 5000
            && $percentageChange !== null
            && abs($percentageChange) < 0.05
        ) {
            return null;
        }

        $increased = $change > 0;

        return $this->storeEvent(
            user: $user,
            snapshot: $snapshot,
            holding: $after,
            type: $increased
                ? TimelineEventType::PortfolioValueIncrease
                : TimelineEventType::PortfolioValueDecrease,
            category: 'holdings',
            severity: 'information',
            headline: sprintf(
                '%s value %s',
                $this->holdingLabel($after),
                $increased
                    ? 'increased'
                    : 'decreased',
            ),
            summary: sprintf(
                '%s changed from $%s to $%s.',
                $this->holdingLabel($after),
                number_format($previousValue, 2),
                number_format($currentValue, 2),
            ),
            before: [
                'market_value' =>
                    $previousValue,

                'quantity' =>
                    (float) $before->quantity,
            ],
            after: [
                'market_value' =>
                    $currentValue,

                'quantity' =>
                    (float) $after->quantity,
            ],
            metrics: [
                'value_change' =>
                    round($change, 2),

                'percentage_change' =>
                    $percentageChange !== null
                        ? round(
                            $percentageChange,
                            8,
                        )
                        : null,

                'quantity_change' =>
                    round(
                        (float) $after->quantity
                        - (float) $before->quantity,
                        8,
                    ),
            ],
            suffix: 'value-change',
        );
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @param array<string, mixed> $metrics
     */
    private function storeEvent(
        User $user,
        PortfolioStateSnapshot $snapshot,
        PortfolioStateSnapshotHolding $holding,
        TimelineEventType $type,
        string $category,
        string $severity,
        string $headline,
        string $summary,
        ?array $before,
        ?array $after,
        array $metrics,
        string $suffix,
    ): TimelineEvent {
        $fingerprint = hash(
            'sha256',
            implode('|', [
                $user->id,
                'portfolio-snapshot',
                $snapshot->id,
                $holding->holding_key,
                $type->value,
                $suffix,
            ]),
        );

        return TimelineEvent::query()
            ->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fingerprint' => $fingerprint,
                ],
                [
                    'event_date' =>
                        $snapshot
                            ->captured_at
                            ->toDateString(),

                    'detected_at' => now(),

                    'type' => $type->value,
                    'category' => $category,
                    'severity' => $severity,
                    'headline' => $headline,
                    'summary' => $summary,
                    'before' => $before,
                    'after' => $after,
                    'metrics' => $metrics,

                    'source_type' =>
                        PortfolioStateSnapshot::class,

                    'source_id' =>
                        $snapshot->id,

                    'route_name' =>
                        'analytics.diversification',

                    'metadata' => [
                        'holding_key' =>
                            $holding->holding_key,

                        'symbol' =>
                            $holding->symbol,

                        'security_id' =>
                            $holding->security_id,

                        'investment_account_id' =>
                            $holding
                                ->investment_account_id,
                    ],
                ],
            );
    }

    private function holdingLabel(
        PortfolioStateSnapshotHolding $holding,
    ): string {
        return $holding->symbol
            ?: $holding->name;
    }
}