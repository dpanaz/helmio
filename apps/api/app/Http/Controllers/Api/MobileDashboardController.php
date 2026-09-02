<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MobileDashboardController extends Controller
{
    public function __construct(
        private readonly HelmScoreService $helmScoreService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'holdings.security',
            ])
            ->get();

        if ($accounts->isEmpty()) {
            return response()->json([
                'status' => 'no_accounts',

                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],

                'portfolio' => [
                    'account_count' => 0,
                    'value' => 0,
                ],

                'helm_score' => [
                    'score' => null,
                    'label' => 'Connect an account',
                    'data_completeness' => 0,
                ],

                'categories' => [],

                'findings' => [],
            ]);
        }

        $analysis =
            $this->helmScoreService->calculate(
                $accounts,
            );

        return response()->json([
            'status' => 'ready',

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'portfolio' => [
                'account_count' =>
                    $accounts->count(),

                'value' =>
                    $this->portfolioValue(
                        $accounts,
                    ),
            ],

            'helm_score' => [
                'score' =>
                    $analysis['overall_score']
                    ?? null,

                'label' =>
                    $analysis['overall_label']
                    ?? 'Building your score',

                'data_completeness' =>
                    $analysis['data_completeness']
                    ?? 0,

                'formula_version' =>
                    $analysis['formula_version']
                    ?? null,
            ],

            'categories' =>
                $this->categories(
                    $analysis['categories']
                    ?? [],
                ),

            'findings' =>
                $this->findings(
                    $analysis['categories']
                    ?? [],
                ),

            'benchmark' =>
                $analysis['benchmark']
                ?? null,

            'analysis_period' =>
                $analysis['analysis_period']
                ?? null,

            'calculated_for_date' =>
                $analysis['calculated_for_date']
                ?? null,
        ]);
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     */
   private function portfolioValue(
    Collection $accounts,
        ): float {
            return round(
                (float) $accounts->sum(
                    function (InvestmentAccount $account): float {
                        if ($account->current_value !== null) {
                            return max(
                                0,
                                (float) $account->current_value,
                            );
                        }

                        $holdingsValue = (float) $account
                            ->holdings
                            ->sum(
                                fn ($holding): float =>
                                    max(
                                        0,
                                        (float) (
                                            $holding->market_value
                                            ?? 0
                                        ),
                                    ),
                            );

                        $cashValue = max(
                            0,
                            (float) (
                                $account->cash_value
                                ?? 0
                            ),
                        );

                        return $holdingsValue + $cashValue;
                    },
                ),
                2,
            );
        }

    /**
     * @param array<string, mixed> $categories
     * @return array<string, mixed>
     */
    private function categories(
        array $categories,
    ): array {
        return collect($categories)
            ->map(
                function (
                    array $category,
                    string $key,
                ): array {
                    return [
                        'key' => $key,

                        'name' =>
                            $this->categoryName(
                                $key,
                            ),

                        'score' =>
                            $category['score']
                            ?? null,

                        'label' =>
                            $category['label']
                            ?? 'Insufficient data',

                        'status' =>
                            $category['status']
                            ?? 'unknown',

                        'reasons' =>
                            array_values(
                                $category['reasons']
                                ?? [],
                            ),

                        'recommendations' =>
                            array_values(
                                $category[
                                    'recommendations'
                                ] ?? [],
                            ),
                    ];
                },
            )
            ->all();
    }

    /**
     * Build a small mobile-friendly list of
     * the most important category findings.
     *
     * @param array<string, mixed> $categories
     * @return array<int, array<string, mixed>>
     */
    private function findings(
        array $categories,
    ): array {
        return collect($categories)
            ->filter(
                fn (array $category): bool =>
                    isset($category['score'])
                    && is_numeric(
                        $category['score'],
                    )
                    && (int) $category['score'] < 80,
            )
            ->sortBy(
                fn (array $category) =>
                    (int) $category['score'],
            )
            ->take(5)
            ->map(
                function (
                    array $category,
                    string $key,
                ): array {
                    $reasons =
                        array_values(
                            $category['reasons']
                            ?? [],
                        );

                    $recommendations =
                        array_values(
                            $category[
                                'recommendations'
                            ] ?? [],
                        );

                    return [
                        'category' => $key,

                        'category_name' =>
                            $this->categoryName(
                                $key,
                            ),

                        'score' =>
                            $category['score']
                            ?? null,

                        'label' =>
                            $category['label']
                            ?? null,

                        'message' =>
                            $reasons[0]
                            ?? null,

                        'recommendation' =>
                            $recommendations[0]
                            ?? null,

                        'severity' =>
                            $this->severity(
                                $category['score']
                                ?? null,
                            ),
                    ];
                },
            )
            ->values()
            ->all();
    }

    private function categoryName(
        string $key,
    ): string {
        return match ($key) {
            'cost' =>
                'Costs',

            'diversification' =>
                'Diversification',

            'performance' =>
                'Performance',

            'risk' =>
                'Risk',

            'trading' =>
                'Trading',

            'cash' =>
                'Cash',

            'tax' =>
                'Tax Efficiency',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $key,
                    ),
                ),
        };
    }

    private function severity(
        mixed $score,
    ): string {
        if (! is_numeric($score)) {
            return 'unknown';
        }

        $score = (int) $score;

        return match (true) {
            $score < 40 =>
                'critical',

            $score < 60 =>
                'important',

            $score < 80 =>
                'attention',

            default =>
                'good',
        };
    }
}