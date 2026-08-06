<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Billing\SubscriptionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly SubscriptionAccessService $subscriptionAccess,
    ) {
    }

    public function index(Request $request): RedirectResponse
    {
        return $this->redirectToNextStep($request->user());
    }

    public function welcome(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->subscriptionAccess->hasPremiumAccess($user)) {
            return redirect()->route('billing.pricing');
        }

        if ($this->hasCompletedInvestorProfile($user)) {
            return redirect()->route('onboarding.connect');
        }

        return view('onboarding.welcome');
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->subscriptionAccess->hasPremiumAccess($user)) {
            return redirect()->route('billing.pricing');
        }

        if ($this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.complete');
        }

        return view('onboarding.profile', [
            'investorProfile' => $user->investorProfile,
        ]);
    }

    public function connect(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->subscriptionAccess->hasPremiumAccess($user)) {
            return redirect()->route('billing.pricing');
        }

        if (! $this->hasCompletedInvestorProfile($user)) {
            return redirect()->route('onboarding.profile');
        }

        if ($this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.syncing');
        }

        return view('onboarding.connect');
    }

    public function syncing(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.connect');
        }

        return view('onboarding.syncing', [
            'accounts' => $user->investmentAccounts()
                ->with('institution')
                ->get(),

            'connections' => $user->brokerageConnections()
                ->latest()
                ->get(),
        ]);
    }

    public function complete(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.connect');
        }

        return view('onboarding.complete', [
            'accountCount' => $user->investmentAccounts()->count(),
            'portfolioValue' => (float) $user
                ->investmentAccounts()
                ->sum('current_value'),
        ]);
    }

    public function finish(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->isComplete($user)) {
            return $this->redirectToNextStep($user);
        }

        return redirect()->route('dashboard');
    }

    public function redirectToNextStep(User $user): RedirectResponse
    {
        if (! $this->subscriptionAccess->hasPremiumAccess($user)) {
            return redirect()->route('billing.pricing');
        }

        if (! $this->hasCompletedInvestorProfile($user)) {
            return redirect()->route('onboarding.welcome');
        }

        if (! $this->hasConnectedAccount($user)) {
            return redirect()->route('onboarding.connect');
        }

        return redirect()->route('onboarding.complete');
    }

    public function isComplete(User $user): bool
    {
        return $this->subscriptionAccess->hasPremiumAccess($user)
            && $this->hasCompletedInvestorProfile($user)
            && $this->hasConnectedAccount($user);
    }

    private function hasCompletedInvestorProfile(User $user): bool
    {
        return $user->investorProfile()->exists();
    }

    private function hasConnectedAccount(User $user): bool
    {
        return $user->investmentAccounts()->exists()
            || $user->brokerageConnections()
                ->whereNotIn('status', [
                    'disabled',
                    'disconnected',
                    'failed',
                ])
                ->exists();
    }
}