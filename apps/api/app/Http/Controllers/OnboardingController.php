<?php

namespace App\Http\Controllers;

use App\Jobs\BuildPortfolioAnalytics;
use App\Jobs\GenerateAiPortfolioInsight;
use App\Jobs\RecalculateAdvisorAuditForUser;
use App\Models\InvestorProfile;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Billing\SubscriptionAccessService;
use App\Services\Dashboard\DashboardService;
use App\Services\Onboarding\ReviewPreparationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly SubscriptionAccessService $subscriptionAccess,
        private readonly ReviewPreparationService $preparation,
    ) {
    }

    public function index(Request $request): RedirectResponse
    {
        return $this->redirectToNextStep($request->user());
    }

    public function welcome(Request $request): RedirectResponse
    {
        return $this->redirectToNextStep($request->user());
    }

    public function profile(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->accessRedirect($request->user())) {
            return $redirect;
        }

        return view('onboarding.profile', ['investorProfile' => $request->user()->investorProfile]);
    }

    public function saveProfile(Request $request, DashboardService $dashboard): RedirectResponse
    {
        if ($redirect = $this->accessRedirect($request->user())) {
            return $redirect;
        }

        $validated = $request->validate([
            'primary_objective' => ['required', Rule::in(array_keys(InvestorProfile::objectiveOptions()))],
            'time_horizon_years' => ['required', 'integer', 'min:1', 'max:60'],
            'investment_experience' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'liquidity_needs' => ['required', Rule::in(['low', 'moderate', 'high'])],
            'risk_tolerance' => ['required', Rule::in(array_keys(InvestorProfile::riskToleranceOptions()))],
        ]);
        InvestorProfile::updateOrCreate(['user_id' => $request->user()->id], $validated);
        $dashboard->clearAdvisorAuditCache($request->user()->id);
        RecalculateAdvisorAuditForUser::dispatch($request->user()->id)->onQueue('analytics');

        return redirect()->route('onboarding.connect');
    }

    public function connect(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user(), false)) {
            return $redirect;
        }
        if ($this->hasConnectedAccount($request->user())) {
            return redirect()->route('onboarding.syncing');
        }

        return view('onboarding.connect');
    }

    public function syncing(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user())) {
            return $redirect;
        }
        $state = $this->preparation->status($request->user());
        if ($state['ready']) {
            return redirect()->route('onboarding.complete');
        }

        return view('onboarding.syncing', ['preparation' => $state]);
    }

    public function status(Request $request): JsonResponse|RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user())) {
            return $redirect;
        }

        return response()->json($this->preparation->status($request->user()));
    }

    public function retry(Request $request): RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user())) {
            return $redirect;
        }
        $run = PortfolioAnalysisRun::query()->where('user_id', $request->user()->id)->latest('id')->first();
        if ($run?->status === PortfolioAnalysisRun::STATUS_FAILED) {
            $claimed = PortfolioAnalysisRun::query()->whereKey($run->id)
                ->where('status', PortfolioAnalysisRun::STATUS_FAILED)
                ->update(['status' => PortfolioAnalysisRun::STATUS_PENDING, 'current_step' => 'starting', 'failed_at' => null]);
            if ($claimed) {
                BuildPortfolioAnalytics::dispatch($run->id);
            }
        } elseif ($run?->status === PortfolioAnalysisRun::STATUS_READY) {
            GenerateAiPortfolioInsight::dispatch($request->user()->id, 'manual_regenerate');
        } else {
            return redirect()->route('brokerage-connections.index');
        }

        return redirect()->route('onboarding.syncing')->with('success', 'Your review is being prepared again.');
    }

    /** All former reveal URLs land on the same first review. */
    public function complete(Request $request, DashboardService $dashboard): View|RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user())) {
            return $redirect;
        }
        if (! $this->preparation->status($request->user())['ready']) {
            return redirect()->route('onboarding.syncing');
        }

        return view('onboarding.complete', ['dashboard' => $dashboard->build($request->user()->id)]);
    }

    public function finish(Request $request): RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($request->user())) {
            return $redirect;
        }
        if (! $this->preparation->status($request->user())['ready']) {
            return redirect()->route('onboarding.syncing');
        }
        $request->user()->forceFill(['onboarding_completed_at' => now()])->save();

        return redirect()->route('dashboard');
    }

    public function redirectToNextStep(User $user): RedirectResponse
    {
        if ($redirect = $this->prerequisiteRedirect($user)) {
            return $redirect;
        }

        return redirect()->route($user->onboarding_completed_at ? 'dashboard' : 'onboarding.syncing');
    }

    public function isComplete(User $user): bool
    {
        return $this->isDemoUser($user) || (
            $this->subscriptionAccess->hasPremiumAccess($user)
            && $this->hasCompletedInvestorProfile($user)
            && $this->hasConnectedAccount($user)
            && $user->onboarding_completed_at !== null
        );
    }

    private function accessRedirect(User $user): ?RedirectResponse
    {
        if ($this->isDemoUser($user)) {
            return redirect()->route('dashboard');
        }

        return $this->subscriptionAccess->hasPremiumAccess($user) ? null : redirect()->route('billing.pricing');
    }

    private function prerequisiteRedirect(User $user, bool $requireConnection = true): ?RedirectResponse
    {
        if ($redirect = $this->accessRedirect($user)) {
            return $redirect;
        }
        if (! $this->hasCompletedInvestorProfile($user)) {
            return redirect()->route('onboarding.profile');
        }
        if ($requireConnection && ! $this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.connect');
        }

        return null;
    }

    private function hasCompletedInvestorProfile(User $user): bool
    {
        $profile = $user->investorProfile;
        // Preserve access for existing customers; new profiles require all five answers.
        return $profile !== null && ($user->onboarding_completed_at !== null || (
            filled($profile->primary_objective) && $profile->time_horizon_years !== null
            && filled($profile->investment_experience) && filled($profile->liquidity_needs)
            && filled($profile->risk_tolerance)
        ));
    }

    private function hasConnectedAccount(User $user): bool
    {
        return $user->investmentAccounts()->exists() || $user->brokerageConnections()
            ->whereNotIn('status', ['disabled', 'disconnected', 'failed'])->exists();
    }

    private function isDemoUser(User $user): bool
    {
        return strtolower((string) $user->email) === 'demo@myhelmio.com';
    }
}
