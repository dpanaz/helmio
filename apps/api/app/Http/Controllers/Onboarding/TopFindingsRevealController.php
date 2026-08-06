<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Benchmark;
use App\Services\AdvisorAudit\AdvisorAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopFindingsRevealController extends Controller
{
    public function index(
        Request $request,
        AdvisorAuditService $advisorAuditService,
    ): View {
        $benchmark = Benchmark::query()
            ->where('is_active', true)
            ->where('symbol', 'SPY')
            ->first();

        $audit = $advisorAuditService->analyze(
            user: $request->user(),
            startDate: Carbon::now()
                ->subYear()
                ->startOfDay(),
            endDate: Carbon::now()
                ->endOfDay(),
            benchmark: $benchmark,
        );

        $critical = collect(
            data_get(
                $audit,
                'findings.critical',
                [],
            ),
        );

        $important = collect(
            data_get(
                $audit,
                'findings.important',
                [],
            ),
        );

        $opportunities = collect(
            data_get(
                $audit,
                'findings.opportunities',
                [],
            ),
        );

        $topConcern = $critical
            ->concat($important)
            ->sortByDesc(
                fn (array $finding): int =>
                    (int) data_get(
                        $finding,
                        'priority',
                        0,
                    ),
            )
            ->first();

        $topOpportunity = $opportunities
            ->sortByDesc(
                fn (array $finding): int =>
                    (int) data_get(
                        $finding,
                        'priority',
                        0,
                    ),
            )
            ->first();

        $strongestCategory = collect(
            data_get(
                $audit,
                'categories',
                [],
            ),
        )
            ->filter(
                fn (array $category): bool =>
                    is_numeric(
                        data_get(
                            $category,
                            'score',
                        ),
                    ),
            )
            ->sortByDesc(
                fn (array $category): int =>
                    (int) data_get(
                        $category,
                        'score',
                        0,
                    ),
            )
            ->map(
                fn (
                    array $category,
                    string $key,
                ): array => [
                    'key' => $key,
                    'label' => str($key)
                        ->replace('_', ' ')
                        ->title()
                        ->toString(),
                    'score' => (int) data_get(
                        $category,
                        'score',
                        0,
                    ),
                    'category_label' => data_get(
                        $category,
                        'label',
                        'Strong',
                    ),
                    'reason' => collect(
                        data_get(
                            $category,
                            'reasons',
                            [],
                        ),
                    )->first(),
                ],
            )
            ->first();

        return view(
            'onboarding.findings',
            [
                'audit' => $audit,
                'topConcern' => $topConcern,
                'topOpportunity' => $topOpportunity,
                'strongestCategory' => $strongestCategory,
            ],
        );
    }
}