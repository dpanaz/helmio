<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-950">
                    Monthly Advisor Audits
                </h2>

                <p class="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500">
                    Schedule recurring Advisor Audits and choose which changes should generate notifications.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('advisor-action-center.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Action Center
                </a>

                <a
                    href="{{ route('advisor-audit.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    Advisor Audit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                    <h3 class="font-semibold text-red-900">
                        Please correct the following:
                    </h3>

                    <ul class="mt-3 space-y-1 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('advisor-audit.monthly-settings.update') }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="max-w-2xl">
                            <h3 class="text-lg font-semibold text-slate-950">
                                Automatic monthly audit
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Helmio will rerun the Advisor Audit each month, save the results, compare them with the prior audit, and update active findings.
                            </p>
                        </div>

                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="hidden"
                                name="is_enabled"
                                value="0"
                            >

                            <input
                                id="is_enabled"
                                name="is_enabled"
                                type="checkbox"
                                value="1"
                                @checked(old('is_enabled', $setting->is_enabled))
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-semibold text-slate-700">
                                Enabled
                            </span>
                        </label>
                    </div>

                    <div class="mt-6 rounded-lg bg-slate-50 p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-700">
                                    Current status
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    @if ($setting->is_enabled)
                                        Monthly Advisor Audits are active.
                                    @else
                                        Monthly Advisor Audits are currently disabled.
                                    @endif
                                </p>
                            </div>

                            <span
                                class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $setting->is_enabled
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-slate-200 text-slate-700' }}"
                            >
                                {{ $setting->is_enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">
                            Schedule
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Choose when Helmio should run the monthly audit.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-3">
                        <div>
                            <label
                                for="run_day"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Day of month
                            </label>

                            <select
                                id="run_day"
                                name="run_day"
                                class="mt-1 block w-full rounded-lg border-slate-300"
                                required
                            >
                                @for ($day = 1; $day <= 28; $day++)
                                    <option
                                        value="{{ $day }}"
                                        @selected(
                                            (int) old(
                                                'run_day',
                                                $setting->run_day
                                            ) === $day
                                        )
                                    >
                                        {{ $day }}
                                    </option>
                                @endfor
                            </select>

                            <p class="mt-1 text-xs text-slate-500">
                                Days 1–28 avoid short-month scheduling conflicts.
                            </p>
                        </div>

                        <div>
                            <label
                                for="timezone"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Timezone
                            </label>

                            <select
                                id="timezone"
                                name="timezone"
                                class="mt-1 block w-full rounded-lg border-slate-300"
                                required
                            >
                                @foreach ($timezones as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'timezone',
                                                $setting->timezone
                                            ) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                for="benchmark_id"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Benchmark
                            </label>

                            <select
                                id="benchmark_id"
                                name="benchmark_id"
                                class="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Default benchmark
                                </option>

                                @foreach ($benchmarks as $benchmark)
                                    <option
                                        value="{{ $benchmark->id }}"
                                        @selected(
                                            (string) old(
                                                'benchmark_id',
                                                $setting->benchmark_id
                                            ) === (string) $benchmark->id
                                        )
                                    >
                                        {{ $benchmark->name }}
                                        ({{ $benchmark->symbol }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Last run
                            </p>

                            <p class="mt-2 text-sm font-medium text-slate-900">
                                {{ $setting->last_run_at
                                    ? $setting->last_run_at
                                        ->timezone($setting->timezone)
                                        ->format('M j, Y g:i A')
                                    : 'No scheduled audit has run yet.' }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Next scheduled run
                            </p>

                            <p class="mt-2 text-sm font-medium text-slate-900">
                                @if ($setting->is_enabled && $setting->next_run_at)
                                    {{ $setting->next_run_at
                                        ->timezone($setting->timezone)
                                        ->format('M j, Y g:i A') }}
                                @elseif ($setting->is_enabled)
                                    The next run will be calculated when these settings are saved.
                                @else
                                    Enable monthly audits to schedule the next run.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">
                            Notifications
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Choose which audit events should appear in Helmio notifications.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4">
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                            <input
                                type="hidden"
                                name="notify_on_completion"
                                value="0"
                            >

                            <input
                                name="notify_on_completion"
                                type="checkbox"
                                value="1"
                                @checked(
                                    old(
                                        'notify_on_completion',
                                        $setting->notify_on_completion
                                    )
                                )
                                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    Audit completed
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Notify me whenever a scheduled monthly audit finishes.
                                </p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                            <input
                                type="hidden"
                                name="notify_on_new_critical"
                                value="0"
                            >

                            <input
                                name="notify_on_new_critical"
                                type="checkbox"
                                value="1"
                                @checked(
                                    old(
                                        'notify_on_new_critical',
                                        $setting->notify_on_new_critical
                                    )
                                )
                                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    New critical findings
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Notify me when the audit detects a critical issue that was not present in the prior run.
                                </p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                            <input
                                type="hidden"
                                name="notify_on_score_change"
                                value="0"
                            >

                            <input
                                id="notify_on_score_change"
                                name="notify_on_score_change"
                                type="checkbox"
                                value="1"
                                @checked(
                                    old(
                                        'notify_on_score_change',
                                        $setting->notify_on_score_change
                                    )
                                )
                                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900">
                                    Material score changes
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    Notify me when the Advisor Audit score changes by at least the selected threshold.
                                </p>

                                <div class="mt-4 max-w-xs">
                                    <label
                                        for="score_change_threshold"
                                        class="block text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Score-change threshold
                                    </label>

                                    <div class="mt-1 flex items-center gap-3">
                                        <input
                                            id="score_change_threshold"
                                            name="score_change_threshold"
                                            type="number"
                                            min="1"
                                            max="25"
                                            value="{{ old(
                                                'score_change_threshold',
                                                $setting->score_change_threshold
                                            ) }}"
                                            class="block w-28 rounded-lg border-slate-300"
                                            required
                                        >

                                        <span class="text-sm text-slate-500">
                                            points
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </section>

                <section class="rounded-xl border border-blue-200 bg-blue-50 p-6">
                    <h3 class="font-semibold text-blue-950">
                        What happens during a scheduled audit?
                    </h3>

                    <div class="mt-4 grid gap-4 text-sm leading-6 text-blue-900 md:grid-cols-2">
                        <div>
                            <p class="font-semibold">
                                Helmio recalculates:
                            </p>

                            <p class="mt-1 text-blue-800">
                                Costs, diversification, performance, risk, trading discipline, cash drag, tax efficiency, and the overall Advisor Audit score.
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold">
                                Helmio updates:
                            </p>

                            <p class="mt-1 text-blue-800">
                                Audit history, active findings, resolved issues, Action Center priorities, dashboard summaries, and notifications.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ route('advisor-audit.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Save Monthly Audit Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>