<?php

namespace Tests\Unit;

use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\AdvisorAudit\AdvisorAuditFindingBuilder;
use App\Services\AdvisorAudit\AdvisorAuditScoringService;
use App\Services\AdvisorAudit\AdvisorAuditService;
use App\Services\Analytics\Cash\CashDragAnalyticsService;
use App\Services\Analytics\CostAnalyticsService;
use App\Services\Analytics\DiversificationAnalyticsService;
use App\Services\Analytics\FundExpenseAnalyticsService;
use App\Services\Analytics\Performance\PerformanceAnalyticsService;
use App\Services\Analytics\Risk\RiskAnalyticsService;
use App\Services\Analytics\Risk\SuitabilityRiskService;
use App\Services\Analytics\Tax\TaxAnalyticsService;
use App\Services\Analytics\Trading\TradingAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdvisorAuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_combines_category_scores_and_findings(): void
    {
        $user = User::factory()->create();

        InvestmentAccount::query()->create([
            'user_id' =>
                $user->id,

            'name' =>
                'Test Account',

            'institution_name' =>
                'Test Institution',

            'account_type' =>
                'brokerage',

            'currency' =>
                'USD',
        ]);

        $benchmark = Benchmark::query()->create([
            'name' =>
                'S&P 500',

            'symbol' =>
                'SPY',

            'benchmark_type' =>
                'market_index',

            'is_active' =>
                true,
        ]);

        $sharedResult = fn (
            int $score,
            string $label
        ): array => [
            'status' =>
                'complete',

            'message' =>
                null,

            'score' =>
                $score,

            'label' =>
                $label,

            'metrics' =>
                [],

            'flags' =>
                [],

            'warnings' =>
                [],

            'data' =>
                [],

            'formula_version' =>
                'test-1.0.0',
        ];

        $costAnalytics = Mockery::mock(
            CostAnalyticsService::class
        );

        $costAnalytics
            ->shouldReceive('calculate')
            ->once()
            ->andReturn([
                'portfolio_value' =>
                    100000,

                'all_in_cost_rate' =>
                    0.01,

                'total_annual_cost' =>
                    1000,
            ]);

        $fundAnalytics = Mockery::mock(
            FundExpenseAnalyticsService::class
        );

        $fundAnalytics
            ->shouldReceive('calculate')
            ->once()
            ->andReturn([
                'expense_data_coverage_rate' =>
                    1,

                'annual_expense_cost' =>
                    250,

                'estimated_savings' =>
                    0,

                'missing_expense_ratio_count' =>
                    0,
            ]);

        $diversification = Mockery::mock(
            DiversificationAnalyticsService::class
        );

        $diversification
            ->shouldReceive('calculate')
            ->once()
            ->andReturn([
                'score' =>
                    80,

                'label' =>
                    'Very good',

                'reasons' =>
                    [],

                'recommendations' =>
                    [],

                'metrics' =>
                    [],
            ]);

        $performance = Mockery::mock(
            PerformanceAnalyticsService::class
        );

        $performance
            ->shouldReceive('analyze')
            ->once()
            ->andReturn(
                $sharedResult(
                    75,
                    'Good'
                )
            );

        $risk = Mockery::mock(
            RiskAnalyticsService::class
        );

        $risk
            ->shouldReceive('analyze')
            ->once()
            ->andReturn(
                $sharedResult(
                    70,
                    'Good'
                )
            );

        $suitability = Mockery::mock(
            SuitabilityRiskService::class
        );

        $suitability
            ->shouldReceive('analyze')
            ->once()
            ->andReturn([
                'status' =>
                    'complete',

                'message' =>
                    null,

                'score' =>
                    80,

                'label' =>
                    'Generally aligned',

                'metrics' => [
                    'actual_risk_level' =>
                        'moderate',

                    'actual_risk_score' =>
                        3,

                    'expected_risk_tolerance' =>
                        'moderate',

                    'expected_risk_score' =>
                        3,

                    'risk_gap' =>
                        0,

                    'profile_completeness' =>
                        1.0,

                    'account_override_count' =>
                        0,
                ],

                'flags' => [
                    [
                        'code' =>
                            'portfolio_risk_aligned',

                        'severity' =>
                            'informational',

                        'title' =>
                            'Portfolio risk aligns with your investor profile',

                        'message' =>
                            'The measured portfolio risk is aligned with the investor profile.',
                    ],
                ],

                'warnings' =>
                    [],

                'recommendations' => [
                    'Continue monitoring suitability as the investor profile changes.',
                ],

                'data' =>
                    [],

                'formula_version' =>
                    'suitability-risk-0.1.0',
            ]);

        $trading = Mockery::mock(
            TradingAnalyticsService::class
        );

        $trading
            ->shouldReceive('analyze')
            ->once()
            ->andReturn(
                $sharedResult(
                    65,
                    'Fair'
                )
            );

        $cash = Mockery::mock(
            CashDragAnalyticsService::class
        );

        $cash
            ->shouldReceive('analyze')
            ->once()
            ->andReturn(
                $sharedResult(
                    90,
                    'Excellent'
                )
            );

        $tax = Mockery::mock(
            TaxAnalyticsService::class
        );

        $tax
            ->shouldReceive('analyze')
            ->once()
            ->andReturn(
                $sharedResult(
                    85,
                    'Very good'
                )
            );

        $service = new AdvisorAuditService(
            costAnalytics:
                $costAnalytics,

            fundAnalytics:
                $fundAnalytics,

            diversificationAnalytics:
                $diversification,

            performanceAnalytics:
                $performance,

            riskAnalytics:
                $risk,

            suitabilityRiskAnalytics:
                $suitability,

            tradingAnalytics:
                $trading,

            cashAnalytics:
                $cash,

            taxAnalytics:
                $tax,

            scoringService:
                new AdvisorAuditScoringService(),

            findingBuilder:
                new AdvisorAuditFindingBuilder(),
        );

        $result = $service->analyze(
            user:
                $user,

            startDate:
                Carbon::parse(
                    '2026-01-01'
                ),

            endDate:
                Carbon::parse(
                    '2026-08-04'
                ),

            benchmark:
                $benchmark,
        );

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertNotNull(
            $result['overall_score']
        );

        $this->assertCount(
            8,
            $result['categories']
        );

        $this->assertArrayHasKey(
            'suitability',
            $result['categories']
        );

        $this->assertSame(
            80,
            $result['categories']
                ['suitability']
                ['score']
        );

        $this->assertArrayHasKey(
            'executive_summary',
            $result
        );

        $this->assertArrayHasKey(
            'findings',
            $result
        );
    }
}