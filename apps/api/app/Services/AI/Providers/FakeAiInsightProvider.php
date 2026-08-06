<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AiInsightProviderInterface;
use Illuminate\Support\Collection;

class FakeAiInsightProvider implements AiInsightProviderInterface
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function modelName(): ?string
    {
        return 'deterministic-development-generator';
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function generate(
        array $context,
    ): array {
        $portfolio = $context['portfolio'];
        $audit = $context['advisor_audit'];
        $freshness = $context['data_freshness'];

        $holdings = collect(
            $context['top_holdings'] ?? [],
        );

        $findings = collect(
            $context['open_findings'] ?? [],
        );

        $largestHolding = $holdings->first();

        $priorities = $findings
            ->whereIn('severity', [
                'critical',
                'high',
                'medium',
            ])
            ->take(3)
            ->map(
                fn (array $finding): array => [
                    'title' => $finding['title'],

                    'reason' =>
                        $finding['description'],

                    'category' =>
                        $finding['category'],

                    'severity' =>
                        $finding['severity'],

                    'route_name' =>
                        $finding['route_name'],
                ],
            )
            ->values();

        $positiveChanges = collect();

        if ($freshness['status'] === 'current') {
            $positiveChanges->push(
                'Connected brokerage data is current.',
            );
        }

        if (
            ($audit['annual_cost'] ?? 0) <= 0
        ) {
            $positiveChanges->push(
                'No material annual portfolio costs are currently recorded.',
            );
        }

        $costScore = data_get(
            $context,
            'helm_score.categories.cost.score',
        );

        if (
            $costScore !== null
            && $costScore >= 80
        ) {
            $positiveChanges->push(
                'Portfolio cost efficiency is currently strong.',
            );
        }

        $headline = $priorities->first()['title']
            ?? 'Portfolio monitoring is up to date';

        $summaryParts = collect([
            sprintf(
                'Your portfolio is valued at $%s across %d account%s.',
                number_format(
                    (float) $portfolio['total_value'],
                    2,
                ),
                (int) $portfolio['account_count'],
                (int) $portfolio['account_count'] === 1
                    ? ''
                    : 's',
            ),
        ]);

        if ($largestHolding !== null) {
            $summaryParts->push(
                sprintf(
                    'The largest reported holding is %s with a market value of $%s.',
                    $largestHolding['symbol']
                        ?: $largestHolding['name'],
                    number_format(
                        (float) $largestHolding['market_value'],
                        2,
                    ),
                ),
            );
        }

        if ($audit['grade'] !== null) {
            $summaryParts->push(
                sprintf(
                    'The current Advisor Audit grade is %s with %d review item%s.',
                    $audit['grade'],
                    (int) $audit['issue_count'],
                    (int) $audit['issue_count'] === 1
                        ? ''
                        : 's',
                ),
            );
        }

        if ($freshness['status'] === 'stale') {
            $summaryParts->push(
                'Some connected data is stale, so conclusions should be reviewed after synchronization.',
            );
        }

        return [
            'headline' => $headline,

            'summary' =>
                $summaryParts->implode(' '),

            'priorities' =>
                $priorities->all(),

            'positive_changes' =>
                $positiveChanges
                    ->take(3)
                    ->values()
                    ->all(),

            'limitations' =>
                $context['limitations'] ?? [],

            'confidence' =>
                $this->confidence($context),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function confidence(
        array $context,
    ): string {
        $freshness = data_get(
            $context,
            'data_freshness.status',
        );

        $completeness = (float) data_get(
            $context,
            'helm_score.data_completeness',
            0,
        );

        if (
            $freshness === 'attention'
            || $freshness === 'stale'
            || $completeness < 0.5
        ) {
            return 'low';
        }

        if ($completeness < 0.8) {
            return 'medium';
        }

        return 'high';
    }
}