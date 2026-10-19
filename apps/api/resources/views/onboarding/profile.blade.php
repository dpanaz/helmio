@extends('layouts.onboarding')

@section('content')
    <nav aria-label="Setup progress">
        <ol class="setup-steps">
            <li aria-current="step"><span class="setup-step-number">1</span>About you</li>
            <li><span class="setup-step-number">2</span>Link accounts</li>
            <li><span class="setup-step-number">3</span>Choose a plan</li>
        </ol>
    </nav>
    <div class="setup-grid">
        <aside class="setup-intro">
            <p class="setup-eyebrow">Step 1 of 3 · About you</p>
            <h1>Welcome, {{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') }}.<br>Let’s make this<br><span>about you.</span></h1>
            <p class="setup-lead">Your goals are the starting point. Tell us about you as an investor. We’ll ask about the purpose and timeframe of each account after you link it.</p>
            <div class="setup-reassurance">
                <div class="setup-note">
                    <span class="setup-note-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m12 3 8 4v5c0 5-8 9-8 9S4 17 4 12V7l8-4Z"/><path d="m8 12 3 3 5-6"/></svg></span>
                    <div><strong>A review that fits your life</strong><p>We’ll compare your portfolio with your overall goals and comfort with risk.</p></div>
                </div>
                <div class="setup-note">
                    <span class="setup-note-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                    <div><strong>A few simple answers</strong><p>Go with what feels right today. You can update your answers as life changes.</p></div>
                </div>
                <p class="setup-next"><strong>Up next: link your accounts.</strong><br>Connections are read-only. Helmio cannot move money or place trades.</p>
            </div>
        </aside>
        <section class="setup-card" aria-labelledby="profile-heading">
            <div class="setup-card-heading"><h2 id="profile-heading">Your investor profile</h2><span class="setup-time">3 quick questions</span></div>
            <p class="setup-card-description">A little context helps us make your review more useful.</p>
            <form method="POST" action="{{ route('onboarding.profile.save') }}">
                @csrf
                @php
                    $questions = [
                        'primary_objective' => ['What would you like your investments to do?', 'Choose the goal that matters most to you.', \App\Models\InvestorProfile::objectiveOptions()],
                        'investment_experience' => ['How familiar are you with investing?', 'Every experience level is welcome.', ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']],
                        'risk_tolerance' => ['How comfortable are you with market ups and downs?', 'This helps us put your portfolio’s risk in context.', \App\Models\InvestorProfile::riskToleranceOptions()],
                    ];
                @endphp
                <div class="setup-fields">
                    @foreach ($questions as $field => [$label, $help, $options])
                        <div class="setup-field {{ $loop->last ? 'setup-field-wide' : '' }}">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <select id="{{ $field }}" name="{{ $field }}" required aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" aria-describedby="{{ $field }}-help{{ $errors->has($field) ? ' '.$field.'-error' : '' }}">
                                <option value="">Choose what fits you</option>
                                @foreach ($options as $value => $text)
                                    <option value="{{ $value }}" @selected(old($field, data_get($investorProfile, $field)) === $value)>{{ $text }}</option>
                                @endforeach
                            </select>
                            <p id="{{ $field }}-help" class="setup-help">{{ $help }}</p>
                            @error($field)<p id="{{ $field }}-error" class="setup-error" role="alert">{{ $message }}</p>@enderror
                        </div>

                    @endforeach
                </div>
                <button type="submit" class="setup-submit">Continue to link accounts <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button>
                <p class="setup-footer-note">You can revisit these answers anytime in your profile.</p>
            </form>
        </section>
    </div>
@endsection
