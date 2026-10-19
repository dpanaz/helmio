<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateAdvisorAuditForUser;
use App\Models\InvestmentAccount;
use App\Services\Analytics\Pipeline\PortfolioAnalyticsDispatcher;
use App\Services\Dashboard\DashboardService;
use App\Services\Onboarding\AccountProfileSetupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingAccountProfileController extends Controller
{
    public function index(Request $request, OnboardingController $onboarding, AccountProfileSetupService $setup): View|RedirectResponse|JsonResponse
    {
        if ($redirect = $onboarding->prerequisiteRedirect($request->user(), false)) {
            return $redirect;
        }
        $user = $request->user();
        $total = $user->investmentAccounts()->count();
        $pending = $setup->pending($user)->count();
        if ($total > 0 && $pending === 0) {
            return $request->expectsJson()
                ? response()->json(['next_url' => route('onboarding.index')])
                : redirect()->route('onboarding.index');
        }
        if ($total === 0 && ! $user->brokerageConnections()->whereNotIn('status', ['disabled', 'disconnected', 'failed'])->exists()) {
            return redirect()->route('onboarding.connect');
        }
        if ($request->expectsJson()) {
            return response()->json(['next_url' => $total > 0 ? route('onboarding.accounts') : null]);
        }

        return view('onboarding.account-profile', [
            'account' => $setup->nextAccount($user),
            'accountNumber' => $total - $pending + 1,
            'totalAccounts' => $total,
            'purposeOptions' => AccountProfileSetupService::PURPOSES,
        ]);
    }

    public function update(Request $request, InvestmentAccount $investmentAccount, DashboardService $dashboard, PortfolioAnalyticsDispatcher $analytics, AccountProfileSetupService $setup): RedirectResponse
    {
        abort_unless((int) $investmentAccount->user_id === (int) $request->user()->id, 403);
        $validated = $request->validate([
            'purpose' => ['required', Rule::in(array_keys(AccountProfileSetupService::PURPOSES))],
            'time_horizon_years_override' => ['required', 'integer', 'min:1', 'max:60'],
            'liquidity_needs_override' => ['required', Rule::in(['low', 'moderate', 'high'])],
        ]);
        $investmentAccount->profile()->updateOrCreate(['investment_account_id' => $investmentAccount->id], $validated);
        $dashboard->clearAdvisorAuditCache($request->user()->id);
        RecalculateAdvisorAuditForUser::dispatch($request->user()->id)->onQueue('analytics');
        if ($setup->nextAccount($request->user()) === null) {
            // An import may already be analyzing the old defaults. Queue a fresh
            // review with the completed account profiles before revealing results.
            $analytics->dispatch($request->user(), 'account_profile_updated', force: true);
        }

        return redirect()->route('onboarding.accounts')->with('success', 'Account profile saved.');
    }
}
