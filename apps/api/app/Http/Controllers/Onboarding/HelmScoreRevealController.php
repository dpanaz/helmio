<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelmScoreRevealController extends Controller
{
    public function index(
        Request $request,
        HelmScoreService $helmScoreService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with([
                'institution',
                'holdings.security',
                'transactions',
            ])
            ->get();

        $score = $helmScoreService->calculate(
            $accounts,
        );

        return view(
            'onboarding.score',
            [
                'helmScore' => $score,
            ],
        );
    }
}