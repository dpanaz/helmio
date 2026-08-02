<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Report delivery
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Monthly Advisor Audit
                </h2>
            </div>

            <a
                href="{{ route('advisor-audit.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Back to Advisor Audit
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route(
                    'advisor-audit.monthly-settings.update'
                ) }}"
                class="space-y-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                @csrf
                @method('PUT')

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <label class="flex items-start gap-4">
                        <input
                            type="checkbox"
                            name="monthly_audit_enabled"
                            value="1"
                            @checked(
                                old(
                                    'monthly_audit_enabled',
                                    $user->monthly_audit_enabled
                                )
                            )
                            class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span>
                            <span class="block font-semibold text-blue-950">
                                Send my monthly Advisor Audit
                            </span>

                            <span class="mt-1 block text-sm leading-6 text-blue-800">
                                Helmio will recalculate your audit and email a
                                PDF report on your selected schedule.
                            </span>
                        </span>
                    </label>
                </div>

                <div>
                    <label
                        for="monthly_audit_email"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Delivery email
                    </label>

                    <input
                        id="monthly_audit_email"
                        name="monthly_audit_email"
                        type="email"
                        value="{{ old(
                            'monthly_audit_email',
                            $user->monthly_audit_email
                                ?: $user->email
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('monthly_audit_email')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label
                            for="monthly_audit_day"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Day of each month
                        </label>

                        <select
                            id="monthly_audit_day"
                            name="monthly_audit_day"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                            @for ($day = 1; $day <= 28; $day++)
                                <option
                                    value="{{ $day }}"
                                    @selected(
                                        (int) old(
                                            'monthly_audit_day',
                                            $user->monthly_audit_day
                                        ) === $day
                                    )
                                >
                                    {{ $day }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label
                            for="monthly_audit_time"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Delivery time
                        </label>

                        <input
                            id="monthly_audit_time"
                            name="monthly_audit_time"
                            type="time"
                            value="{{ old(
                                'monthly_audit_time',
                                substr(
                                    $user->monthly_audit_time
                                        ?: '08:00',
                                    0,
                                    5
                                )
                            ) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <div>
                    <label
                        for="timezone"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Time zone
                    </label>

                    <select
                        id="timezone"
                        name="timezone"
                        required
                        class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    >
                        @foreach ($timezones as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'timezone',
                                        $user->timezone
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">
                        What the monthly email includes
                    </p>

                    <ul class="mt-3 space-y-2 text-sm leading-6 text-slate-600">
                        <li>Current Advisor Audit grade and Helm Score</li>
                        <li>New, improved, worsened and resolved findings</li>
                        <li>Annual costs and estimated potential savings</li>
                        <li>Category scores and priority review items</li>
                        <li>A downloadable PDF audit report</li>
                    </ul>
                </div>

                @if ($user->last_monthly_audit_sent_at)
                    <p class="text-sm text-slate-500">
                        Last delivered:
                        {{ $user->last_monthly_audit_sent_at
                            ->timezone($user->timezone)
                            ->format('F j, Y g:i A T') }}
                    </p>
                @endif

                <div class="flex justify-end border-t border-slate-200 pt-6">
                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Save delivery settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
