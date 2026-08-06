<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Models\InvestmentAccountProfile;
use App\Models\InvestorProfile;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentAccountProfileController extends Controller
{
    public function edit(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): View {
        $this->authorizeAccount(
            $request,
            $investmentAccount
        );

        $investmentAccount->loadMissing([
            'institution',
            'user.investorProfile',
            'profile',
        ]);

        $profile =
            $investmentAccount->profile
            ?? new InvestmentAccountProfile([
                'investment_account_id' =>
                    $investmentAccount->id,
            ]);

        return view('accounts.profile.edit', [
            'account' => $investmentAccount,
            'profile' => $profile,
            'investorProfile' =>
                $investmentAccount->user?->investorProfile,
            'riskOptions' =>
                InvestorProfile::riskToleranceOptions(),
            'objectiveOptions' =>
                InvestorProfile::objectiveOptions(),
            'purposeOptions' => [
                'retirement' => 'Retirement',
                'taxable_investing' => 'Taxable Investing',
                'income' => 'Income',
                'education' => 'Education',
                'emergency_reserve' => 'Emergency Reserve',
                'short_term_goal' => 'Short-Term Goal',
                'estate_or_trust' => 'Estate or Trust',
                'other' => 'Other',
            ],
            'liquidityOptions' => [
                'low' => 'Low',
                'moderate' => 'Moderate',
                'high' => 'High',
            ],
        ]);
    }

    public function update(
        Request $request,
        InvestmentAccount $investmentAccount,
        DashboardService $dashboardService,
    ): RedirectResponse {
        $this->authorizeAccount(
            $request,
            $investmentAccount
        );

        $validated = $request->validate([
            'purpose' => [
                'nullable',
                'string',
                'max:50',
            ],
            'target_date' => [
                'nullable',
                'date',
            ],
            'risk_tolerance_override' => [
                'nullable',
                'string',
                'max:40',
            ],
            'objective_override' => [
                'nullable',
                'string',
                'max:40',
            ],
            'time_horizon_years_override' => [
                'nullable',
                'integer',
                'min:1',
                'max:60',
            ],
            'liquidity_needs_override' => [
                'nullable',
                'string',
                'max:40',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        InvestmentAccountProfile::query()
            ->updateOrCreate(
                [
                    'investment_account_id' =>
                        $investmentAccount->id,
                ],
                $validated,
            );

        $dashboardService->clearAdvisorAuditCache(
            $request->user()->id
        );

        return redirect()
            ->route(
                'accounts.profile.edit',
                $investmentAccount
            )
            ->with(
                'success',
                'Account suitability profile updated successfully.'
            );
    }

    private function authorizeAccount(
        Request $request,
        InvestmentAccount $investmentAccount
    ): void {
        abort_unless(
            (int) $investmentAccount->user_id
                === (int) $request->user()->id,
            403
        );
    }
}