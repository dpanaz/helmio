<?php

namespace App\Http\Controllers;

use App\Models\HelmScoreSnapshot;
use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelmScoreController extends Controller
{
    public function index(
        Request $request,
        HelmScoreService $helmScoreService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'institution',
                'holdings.security',
                'transactions',
            ])
            ->get();

        $score = $helmScoreService->calculate($accounts);

        HelmScoreSnapshot::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'calculated_for_date' =>
                    $score['calculated_for_date'],
                'formula_version' =>
                    $score['formula_version'],
            ],
            [
                'overall_score' => $score['overall_score'],
                'cost_score' =>
                    $score['categories']['cost']['score'],
                'diversification_score' => $score['categories']['diversification']['score'],
                'performance_score' => null,
                'risk_score' => null,
                'trading_score' => null,
                'tax_score' => null,
                'data_completeness' =>
                    $score['data_completeness'],
                'score_details' => $score,
            ],
        );

        return view('analytics.helm-score', [
            'helmScore' => $score,
        ]);
    }
}
