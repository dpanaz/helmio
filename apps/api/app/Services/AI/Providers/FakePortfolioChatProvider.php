<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\PortfolioChatProviderInterface;

class FakePortfolioChatProvider implements PortfolioChatProviderInterface
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function modelName(): ?string
    {
        return 'deterministic-portfolio-assistant';
    }

    public function answer(
        string $question,
        array $context,
        array $history = [],
    ): array {
        $normalized = strtolower($question);

        $review = $context[
            'latest_monthly_review'
        ] ?? null;

        $audit = $context[
            'latest_audit'
        ] ?? null;

        $snapshot = $context[
            'portfolio_snapshot'
        ] ?? null;

        $events = collect(
            $context[
                'recent_timeline_events'
            ] ?? [],
        );

        $findings = collect(
            $context[
                'open_findings'
            ] ?? [],
        );

        $citations = collect();
        $limitations = collect(
            $context['limitations'] ?? [],
        );

        if (
            str_contains($normalized, 'what changed')
            || str_contains($normalized, 'this month')
            || str_contains($normalized, 'last month')
        ) {
            if ($review !== null) {
                $citations->push([
                    'type' =>
                        'monthly_portfolio_review',
                    'id' => $review['id'],
                    'label' =>
                        $review['period'].' Monthly Review',
                    'route_name' =>
                        'monthly-reviews.show',
                    'route_parameter' =>
                        $review['id'],
                ]);

                return [
                    'answer' =>
                        $review['summary'],

                    'confidence' => 'high',
                    'citations' =>
                        $citations->all(),

                    'limitations' =>
                        $limitations->all(),
                ];
            }

            $recent = $events->take(5);

            return [
                'answer' => $recent->isEmpty()
                    ? 'No material portfolio changes have been recorded yet.'
                    : $recent
                        ->map(
                            fn (array $event): string =>
                                $event['headline']
                                .': '
                                .($event['summary'] ?? ''),
                        )
                        ->implode(' '),

                'confidence' => $recent->isEmpty()
                    ? 'low'
                    : 'medium',

                'citations' =>
                    $recent
                        ->map(
                            fn (array $event): array => [
                                'type' =>
                                    'timeline_event',
                                'id' => $event['id'],
                                'label' =>
                                    $event['headline'],
                                'route_name' =>
                                    'portfolio-timeline.index',
                            ],
                        )
                        ->values()
                        ->all(),

                'limitations' =>
                    $limitations->all(),
            ];
        }

        if (
            str_contains($normalized, 'score')
            || str_contains($normalized, 'grade')
        ) {
            if ($audit === null) {
                return [
                    'answer' =>
                        'A recorded Advisor Audit is required before I can explain the current score.',

                    'confidence' => 'low',
                    'citations' => [],
                    'limitations' =>
                        $limitations->all(),
                ];
            }

            $citations->push([
                'type' => 'audit_run',
                'id' => $audit['id'],
                'label' => 'Latest Advisor Audit',
                'route_name' =>
                    'advisor-audit.history.show',
                'route_parameter' =>
                    $audit['id'],
            ]);

            return [
                'answer' => sprintf(
                    'Your latest Advisor Audit score is %s with a grade of %s. The audit currently reports %d review item%s and estimated annual costs of $%s.',
                    $audit['score'] ?? 'not calculated',
                    $audit['grade'] ?? 'not calculated',
                    (int) ($audit['issue_count'] ?? 0),
                    (int) ($audit['issue_count'] ?? 0) === 1
                        ? ''
                        : 's',
                    number_format(
                        (float) (
                            $audit['annual_cost']
                            ?? 0
                        ),
                        2,
                    ),
                ),

                'confidence' => 'high',
                'citations' =>
                    $citations->all(),

                'limitations' =>
                    $limitations->all(),
            ];
        }

        if (
            str_contains($normalized, 'review first')
            || str_contains($normalized, 'priority')
            || str_contains($normalized, 'attention')
        ) {
            $priority = $findings->first();

            if ($priority === null) {
                return [
                    'answer' =>
                        'No open priority finding is currently available.',

                    'confidence' => 'medium',
                    'citations' => [],
                    'limitations' =>
                        $limitations->all(),
                ];
            }

            return [
                'answer' => sprintf(
                    'The first item to review is “%s.” %s %s',
                    $priority['title'],
                    $priority['description'],
                    $priority['recommendation']
                        ?? '',
                ),

                'confidence' => 'high',

                'citations' => [[
                    'type' => 'audit_finding',
                    'id' => $priority['id'],
                    'label' =>
                        $priority['title'],
                    'route_name' =>
                        $priority['route_name'],
                ]],

                'limitations' =>
                    $limitations->all(),
            ];
        }

        if (
            str_contains($normalized, 'portfolio')
            || str_contains($normalized, 'value')
            || str_contains($normalized, 'holding')
        ) {
            if ($snapshot === null) {
                return [
                    'answer' =>
                        'No saved portfolio snapshot is available.',

                    'confidence' => 'low',
                    'citations' => [],
                    'limitations' =>
                        $limitations->all(),
                ];
            }

            $largestHolding = collect(
                $snapshot['top_holdings'] ?? [],
            )->first();

            return [
                'answer' => sprintf(
                    'The latest recorded portfolio value is $%s across %d account%s and %d holding%s.%s',
                    number_format(
                        (float) $snapshot[
                            'portfolio_value'
                        ],
                        2,
                    ),
                    (int) $snapshot['account_count'],
                    (int) $snapshot['account_count'] === 1
                        ? ''
                        : 's',
                    (int) $snapshot['holding_count'],
                    (int) $snapshot['holding_count'] === 1
                        ? ''
                        : 's',
                    $largestHolding !== null
                        ? sprintf(
                            ' The largest reported holding is %s at $%s.',
                            $largestHolding['symbol']
                                ?: $largestHolding['name'],
                            number_format(
                                (float) $largestHolding[
                                    'market_value'
                                ],
                                2,
                            ),
                        )
                        : '',
                ),

                'confidence' => 'high',

                'citations' => [[
                    'type' =>
                        'portfolio_state_snapshot',
                    'id' => $snapshot['id'],
                    'label' =>
                        'Latest Portfolio Snapshot',
                    'route_name' =>
                        'portfolio-timeline.index',
                ]],

                'limitations' =>
                    $limitations->all(),
            ];
        }

        return [
            'answer' =>
                'I can explain your latest portfolio value, Advisor Audit score, priority findings, recent timeline changes, and monthly review. Try asking, “What changed this month?” or “What should I review first?”',

            'confidence' => 'medium',
            'citations' => [],
            'limitations' =>
                $limitations->all(),
        ];
    }
}