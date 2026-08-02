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

        $snapshot = HelmScoreSnapshot::query()
            ->where('user_id', $request->user()->id)
            ->whereDate(
                'calculated_for_date',
                $score['calculated_for_date'],
            )
            ->where(
                'formula_version',
                $score['formula_version'],
            )
            ->first();

        if ($snapshot === null) {
            $snapshot = new HelmScoreSnapshot([
                'user_id' => $request->user()->id,
                'calculated_for_date' =>
                    $score['calculated_for_date'],
                'formula_version' =>
                    $score['formula_version'],
            ]);
        }

        $snapshot->fill([
            'overall_score' => $score['overall_score'],
            'cost_score' =>
                $score['categories']['cost']['score'],
            'diversification_score' =>
                $score['categories']['diversification']['score'],
            'performance_score' =>
    $score['categories']['performance']['score'],
            'risk_score' =>
    $score['categories']['risk']['score'],
            'trading_score' =>
    $score['categories']['trading']['score'],
            'tax_score' =>
    $score['categories']['tax']['score'],
            'data_completeness' =>
                $score['data_completeness'],
            'score_details' => $score,
        ]);

        $snapshot->save();

        return view('analytics.helm-score', [
            'helmScore' => $score,
        ]);
    }
}