@extends('layouts.onboarding')
@section('title', 'Account profile')

@section('content')
    <nav aria-label="Setup progress">
        <ol class="setup-steps">
            <li><span class="setup-step-number">✓</span>About you</li>
            <li aria-current="step"><span class="setup-step-number">2</span>Account profiles</li>
            <li><span class="setup-step-number">3</span>Choose a plan</li>
        </ol>
    </nav>
    <div class="setup-grid">
        <aside class="setup-intro">
            <p class="setup-eyebrow">Step 2 of 3 · Your accounts</p>
            <h1>Every account<br>has a <span>purpose.</span></h1>
            <p class="setup-lead">Retirement money and next year’s house deposit have different jobs. Tell us what the money in each account is for, and when you expect to need it.</p>
            <div class="setup-reassurance">
                <p class="setup-next"><strong>Your timeframe belongs to this account.</strong><br>Helmio uses these answers alongside your Investor Profile to assess whether each account fits its intended purpose.</p>
                <p class="setup-next">Your overall goals, experience, and comfort with risk stay in your <a href="{{ route('onboarding.profile') }}">Investor Profile</a>.</p>
            </div>
        </aside>
        <section class="setup-card" aria-labelledby="account-heading">
            @if ($account)
                <div class="setup-card-heading"><h2 id="account-heading">{{ $account->name }}</h2><span class="setup-time">Account {{ $accountNumber }} of {{ $totalAccounts }}</span></div>
                <p class="setup-card-description">{{ $account->institution?->name ?? 'Investment account' }}@if ($account->account_number_mask) · Ending {{ $account->account_number_mask }}@endif<br>Set the purpose and timeframe for the money in this account.</p>
                @if (session('success'))<p class="setup-help" role="status">{{ session('success') }}</p>@endif
                <form method="POST" action="{{ route('onboarding.accounts.update', $account) }}">
                    @csrf
                    @method('PUT')
                    <div class="setup-fields">
                        <div class="setup-field setup-field-wide">
                            <label for="purpose">What is this account for?</label>
                            <select id="purpose" name="purpose" required aria-invalid="{{ $errors->has('purpose') ? 'true' : 'false' }}" aria-describedby="purpose-help purpose-error">
                                <option value="">Choose this account’s purpose</option>
                                @foreach ($purposeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('purpose', $account->profile?->purpose) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="purpose-help" class="setup-help">For example, retirement, education, or an emergency reserve.</p>
                            <p id="purpose-error" class="setup-error">@error('purpose'){{ $message }}@enderror</p>
                        </div>
                        <div class="setup-field setup-field-wide">
                            <label for="time_horizon_years_override">When do you expect to need money from this account?</label>
                            <input id="time_horizon_years_override" name="time_horizon_years_override" type="number" min="1" max="60" required placeholder="Years from now, e.g. 10" value="{{ old('time_horizon_years_override', $account->profile?->time_horizon_years_override) }}" aria-invalid="{{ $errors->has('time_horizon_years_override') ? 'true' : 'false' }}" aria-describedby="timeframe-help timeframe-error">
                            <p id="timeframe-help" class="setup-help">Enter 1 for money you expect to need within the next year. Each account can have a different timeframe.</p>
                            <p id="timeframe-error" class="setup-error">@error('time_horizon_years_override'){{ $message }}@enderror</p>
                        </div>
                        <div class="setup-field setup-field-wide">
                            <label for="liquidity_needs_override">How often will you withdraw from this account?</label>
                            <select id="liquidity_needs_override" name="liquidity_needs_override" required aria-invalid="{{ $errors->has('liquidity_needs_override') ? 'true' : 'false' }}" aria-describedby="withdrawal-error">
                                <option value="">Choose what fits this account</option>
                                @foreach (['low' => 'Rarely — little near-term need', 'moderate' => 'Sometimes — planned withdrawals', 'high' => 'Often — regular or larger withdrawals'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('liquidity_needs_override', $account->profile?->liquidity_needs_override) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="withdrawal-error" class="setup-error">@error('liquidity_needs_override'){{ $message }}@enderror</p>
                        </div>
                    </div>
                    <button class="setup-submit" type="submit">{{ $accountNumber < $totalAccounts ? 'Save and continue to next account' : 'Save account profile and continue' }} →</button>
                    <p class="setup-footer-note">You can update each account profile later.</p>
                </form>
            @else
                <h2 id="account-heading">Bringing in your accounts</h2>
                <p class="setup-card-description">Once your brokerage sends the account list, we’ll help you set a profile for each account.</p>
                <div x-data="{
                    timer: null,
                    stopped: false,
                    delayed: false,
                    attempts: 0,
                    init() { this.poll(); },
                    destroy() { this.stopped = true; clearTimeout(this.timer); },
                    async poll() {
                        try {
                            const response = await fetch(@js(route('onboarding.accounts')), { headers: { Accept: 'application/json' }, cache: 'no-store' });
                            if (response.ok && !response.redirected) {
                                const status = await response.json();
                                if (status.next_url) { window.location.assign(status.next_url); return; }
                            }
                        } catch (error) {}
                        this.attempts++;
                        if (this.attempts >= 24) { this.delayed = true; return; }
                        if (!this.stopped) this.timer = setTimeout(() => this.poll(), 5000);
                    }
                }">
                    <p class="setup-help" role="status">Waiting for your account list…</p>
                    <p class="setup-help" x-show="delayed" x-cloak>This is taking longer than usual. You can check again or contact us for help.</p>
                    <a class="setup-submit" href="{{ route('onboarding.accounts') }}">Check again</a>
                    <p class="setup-footer-note"><a href="{{ route('support.index') }}">Get help with this connection</a></p>
                </div>
            @endif
        </section>
    </div>
@endsection
