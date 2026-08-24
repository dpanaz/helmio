<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use App\Services\Audit\AdvisorAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopFindingsRevealController extends Controller
{
    public function index(
        Request $request,
        AdvisorAuditService $advisorAuditService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'holdings.security',
                'transactions.security',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        $audit = $advisorAuditService->build(
            $accounts,
        );

        $reviewFindings = collect(
            data_get(
                $audit,
                'review_findings',
                [],
            ),
        );

        $positiveFindings = collect(
            data_get(
                $audit,
                'positive_findings',
                [],
            ),
        );

        $topConcern = $reviewFindings
            ->sortBy(
                fn (array $finding): int =>
                    $this->severityRank(
                        (string) data_get(
                            $finding,
                            'severity',
                            '',
                        ),
                    ),
            )
            ->first();

        $topOpportunity = $positiveFindings
            ->sortByDesc(
                fn (array $finding): int =>
                    (int) data_get(
                        $finding,
                        'score',
                        0,
                    ),
            )
            ->first();

        $strongestCategory = collect(
            data_get(
                $audit,
                'category_scores',
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

                    'category_label' =>
                        data_get(
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

    private function severityRank(
        string $severity,
    ): int {
        return match ($severity) {
            'critical' => 1,
            'high' => 2,
            'medium' => 3,
            'low' => 4,
            'information' => 5,
            'positive' => 6,
            default => 7,
        };
    }
}