<?php

namespace App\Http\Controllers;

use App\Models\PortfolioAnalysisRun;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardService $dashboardService,
    ): View {
        $userId = $request->user()->id;

        $dashboard = $dashboardService->build(
            $userId,
        );

        $analysisRun = $this->latestAnalysisRun(
            $userId,
        );

        return view('dashboard', [
            'dashboard' =>
                $dashboard,

            'accounts' =>
                $dashboard['accounts']
                ?? collect(),

            'portfolioValue' =>
                $dashboard['portfolioValue']
                ?? 0,

            'cashValue' =>
                $dashboard['cashValue']
                ?? 0,

            'accountCount' =>
                $dashboard['accountCount']
                ?? 0,

            'helmScore' =>
                $dashboard['helm']
                ?? null,

            'largestAccount' =>
                $dashboard['largestAccount']
                ?? null,

            'advisorAudit' =>
                $dashboard['advisorAudit']
                ?? null,

            'currentAuditRun' =>
                $dashboard['currentAuditRun']
                ?? null,

            'previousAuditRun' =>
                $dashboard['previousAuditRun']
                ?? null,

            'auditComparison' =>
                $dashboard['auditComparison']
                ?? null,

            'openFindings' =>
                $dashboard['openFindings']
                ?? collect(),

            'findingCounts' =>
                $dashboard['findingCounts']
                ?? [],

            'latestAiInsight' =>
                $dashboard['latestAiInsight']
                ?? null,

            'analysisRun' =>
                $analysisRun,

            'analysisState' =>
                $this->analysisState(
                    $analysisRun,
                ),
        ]);
    }

    /**
     * Lightweight endpoint used by the dashboard while an
     * analysis is running.
     */
    public function analysisStatus(
        Request $request,
    ): JsonResponse {
        $run = $this->latestAnalysisRun(
            $request->user()->id,
        );

        $state = $this->analysisState(
            $run,
        );

        return response()->json([
            ...$state,

            'run_id' =>
                $run?->id,

            'started_at' =>
                $run?->started_at
                    ?->toIso8601String(),

            'completed_at' =>
                $run?->completed_at
                    ?->toIso8601String(),

            'metadata' =>
                $run?->metadata ?? [],
        ]);
    }

    private function latestAnalysisRun(
        int $userId,
    ): ?PortfolioAnalysisRun {
        return PortfolioAnalysisRun::query()
            ->where(
                'user_id',
                $userId,
            )
            ->latest('id')
            ->first();
    }

    /**
     * Convert the backend analysis state into something
     * intentionally designed for the customer-facing UI.
     *
     * @return array<string, mixed>
     */
    private function analysisState(
        ?PortfolioAnalysisRun $run,
    ): array {
        if ($run === null) {
            return [
                'status' => 'none',
                'step' => null,
                'is_running' => false,
                'is_ready' => false,
                'has_failed' => false,
                'progress' => 0,
                'headline' =>
                    'Ready to analyze your portfolio',
                'message' =>
                    'Connect an investment account to begin.',
                'steps' =>
                    $this->analysisSteps(null),
            ];
        }

        $status = strtolower(
            (string) $run->status,
        );

        $step = strtolower(
            (string) (
                $run->current_step
                ?? $run->step
                ?? ''
            ),
        );

        $isReady =
            $status === 'ready'
            || $step === 'complete';

        $hasFailed = in_array(
            $status,
            [
                'failed',
                'error',
            ],
            true,
        );

        $isRunning =
            ! $isReady
            && ! $hasFailed;

        if ($isReady) {
            return [
                'status' => $status,
                'step' => $step,
                'is_running' => false,
                'is_ready' => true,
                'has_failed' => false,
                'progress' => 100,
                'headline' =>
                    'Your portfolio analysis is ready',
                'message' =>
                    'Helmio is monitoring your investments.',
                'steps' =>
                    $this->analysisSteps(
                        'complete',
                    ),
            ];
        }

        if ($hasFailed) {
            return [
                'status' => $status,
                'step' => $step,
                'is_running' => false,
                'is_ready' => false,
                'has_failed' => true,
                'progress' => 0,
                'headline' =>
                    'We hit a problem while analyzing your portfolio',
                'message' =>
                    'Your account remains connected. Helmio will try again during the next synchronization.',
                'steps' =>
                    $this->analysisSteps(
                        $step,
                    ),
            ];
        }

        $progress =
            $this->progressForStep(
                $step,
            );

        return [
            'status' => $status,
            'step' => $step,
            'is_running' => $isRunning,
            'is_ready' => false,
            'has_failed' => false,
            'progress' => $progress,
            'headline' =>
                $this->headlineForStep(
                    $step,
                ),
            'message' =>
                $this->messageForStep(
                    $step,
                ),
            'steps' =>
                $this->analysisSteps(
                    $step,
                ),
        ];
    }

    private function progressForStep(
        string $step,
    ): int {
        return match ($step) {
            'syncing',
            'brokerage_sync',
            'brokerage',
            'accounts',
            'holdings',
            'transactions' =>
                20,

            'historical_prices',
            'price_backfill',
            'market_data',
            'prices' =>
                40,

            'portfolio_valuations',
            'valuation_history',
            'valuations',
            'building_history' =>
                60,

            'analytics',
            'analysis',
            'analyzing' =>
                80,

            'helm_score',
            'score',
            'calculating_score' =>
                90,

            'complete' =>
                100,

            default =>
                10,
        };
    }

    private function headlineForStep(
        string $step,
    ): string {
        return match ($step) {
            'syncing',
            'brokerage_sync',
            'brokerage',
            'accounts',
            'holdings',
            'transactions' =>
                'Syncing your investments',

            'historical_prices',
            'price_backfill',
            'market_data',
            'prices' =>
                'Building market history',

            'portfolio_valuations',
            'valuation_history',
            'valuations',
            'building_history' =>
                'Building your portfolio history',

            'analytics',
            'analysis',
            'analyzing' =>
                'Analyzing your portfolio',

            'helm_score',
            'score',
            'calculating_score' =>
                'Calculating your Helm Score',

            default =>
                'Building your Helm Score',
        };
    }

    private function messageForStep(
        string $step,
    ): string {
        return match ($step) {
            'syncing',
            'brokerage_sync',
            'brokerage',
            'accounts',
            'holdings',
            'transactions' =>
                'Helmio is securely importing your latest holdings and transactions.',

            'historical_prices',
            'price_backfill',
            'market_data',
            'prices' =>
                'We’re gathering the market history needed to evaluate your investments.',

            'portfolio_valuations',
            'valuation_history',
            'valuations',
            'building_history' =>
                'We’re reconstructing how your portfolio has changed over time.',

            'analytics',
            'analysis',
            'analyzing' =>
                'Helmio is evaluating costs, diversification, performance, risk, trading, cash, and taxes.',

            'helm_score',
            'score',
            'calculating_score' =>
                'Your analysis is complete. We’re calculating your overall portfolio health score.',

            default =>
                'Helmio is preparing your portfolio analysis. This usually takes less than a minute.',
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function analysisSteps(
        ?string $currentStep,
    ): array {
        $currentStep = strtolower(
            (string) $currentStep,
        );

        $currentIndex = match ($currentStep) {
            'syncing',
            'brokerage_sync',
            'brokerage',
            'accounts',
            'holdings',
            'transactions' =>
                0,

            'historical_prices',
            'price_backfill',
            'market_data',
            'prices' =>
                1,

            'portfolio_valuations',
            'valuation_history',
            'valuations',
            'building_history' =>
                2,

            'analytics',
            'analysis',
            'analyzing' =>
                3,

            'helm_score',
            'score',
            'calculating_score' =>
                4,

            'complete' =>
                5,

            default =>
                0,
        };

        $labels = [
            'Sync investments',
            'Gather market history',
            'Build portfolio history',
            'Analyze portfolio',
            'Calculate Helm Score',
        ];

        return collect($labels)
            ->values()
            ->map(
                function (
                    string $label,
                    int $index,
                ) use (
                    $currentIndex,
                    $currentStep,
                ): array {
                    if (
                        $currentStep === 'complete'
                        || $currentIndex > $index
                    ) {
                        $status = 'complete';
                    } elseif (
                        $currentIndex === $index
                    ) {
                        $status = 'active';
                    } else {
                        $status = 'pending';
                    }

                    return [
                        'label' => $label,
                        'status' => $status,
                    ];
                }
            )
            ->all();
    }
}