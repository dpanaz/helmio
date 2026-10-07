<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">About you</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <section class="mx-auto max-w-3xl rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-10">
            <p class="text-sm font-semibold text-blue-400">Step 1 of 3 · About you</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Welcome to Helmio.</h1>
            <p class="mt-3 text-slate-400">Five answers help us review whether your investments fit your needs. You can update them later.</p>
            <form method="POST" action="{{ route('onboarding.profile.save') }}" class="mt-8 space-y-6">
                @csrf
                @php
                    $questions = [
                        'primary_objective' => ['What is your main investment goal?', 'This gives your review a goal to compare against.', \App\Models\InvestorProfile::objectiveOptions()],
                        'investment_experience' => ['How familiar are you with investing?', 'This helps put investment complexity in context.', ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']],
                        'liquidity_needs' => ['How much access to this money do you need?', 'This helps us review cash availability.', ['low' => 'Low — little near-term need', 'moderate' => 'Moderate — some planned withdrawals', 'high' => 'High — regular or significant withdrawals']],
                        'risk_tolerance' => ['How much fluctuation are you comfortable with?', 'This helps us compare portfolio risk with your comfort level.', \App\Models\InvestorProfile::riskToleranceOptions()],
                    ];
                @endphp
                @foreach ($questions as $field => [$label, $help, $options])
                    <div>
                        <label for="{{ $field }}" class="block font-medium text-white">{{ $label }}</label>
                        <p id="{{ $field }}-help" class="mt-1 text-sm text-slate-400">{{ $help }}</p>
                        <select id="{{ $field }}" name="{{ $field }}" required aria-describedby="{{ $field }}-help {{ $field }}-error" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">
                            <option value="">Choose an answer</option>
                            @foreach ($options as $value => $text)
                                <option value="{{ $value }}" @selected(old($field, data_get($investorProfile, $field)) === $value)>{{ $text }}</option>
                            @endforeach
                        </select>
                        <p id="{{ $field }}-error" class="mt-1 text-sm text-red-300">@error($field) {{ $message }} @enderror</p>
                    </div>
                    @if ($loop->first)
                        <div>
                            <label for="time_horizon_years" class="block font-medium text-white">When do you expect to need this money?</label>
                            <p id="horizon-help" class="mt-1 text-sm text-slate-400">Enter years. This helps us review whether the investments fit your timeframe.</p>
                            <input type="number" id="time_horizon_years" name="time_horizon_years" min="1" max="60" required value="{{ old('time_horizon_years', data_get($investorProfile, 'time_horizon_years')) }}" aria-describedby="horizon-help horizon-error" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">
                            <p id="horizon-error" class="mt-1 text-sm text-red-300">@error('time_horizon_years') {{ $message }} @enderror</p>
                        </div>
                    @endif
                @endforeach
                <button class="w-full rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-500">Continue to connect</button>
            </form>
        </section>
    </div>
</x-app-layout>
