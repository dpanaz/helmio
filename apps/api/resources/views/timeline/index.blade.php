<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Change intelligence
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Portfolio Timeline
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    A chronological record of material portfolio and audit changes.
                </p>
            </div>

            <a
                href="{{ route('advisor-audit.history') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Audit history
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Timeline events
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $eventCount }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Positive changes
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-emerald-700">
                        {{ $positiveCount }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Critical changes
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-red-700">
                        {{ $criticalCount }}
                    </p>
                </article>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form
                    method="GET"
                    action="{{ route('portfolio-timeline.index') }}"
                    class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]"
                >
                    <select
                        name="category"
                        class="rounded-xl border-slate-300"
                    >
                        <option value="">All categories</option>

                        @foreach ([
                            'overall',
                            'audit',
                            'portfolio',
                            'holdings',
                            'cost',
                            'diversification',
                            'performance',
                            'risk',
                            'trading',
                            'tax',
                        ] as $category)
                            <option
                                value="{{ $category }}"
                                @selected(
                                    request('category') === $category
                                )
                            >
                                {{ str($category)->title() }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="severity"
                        class="rounded-xl border-slate-300"
                    >
                        <option value="">All severities</option>

                        @foreach ([
                            'critical',
                            'high',
                            'medium',
                            'low',
                            'information',
                            'positive',
                        ] as $severity)
                            <option
                                value="{{ $severity }}"
                                @selected(
                                    request('severity') === $severity
                                )
                            >
                                {{ str($severity)->title() }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-700"
                    >
                        Filter
                    </button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                @forelse ($events as $event)
                    @php
                        $severityClasses = match ($event->severity) {
                            'critical' =>
                                'bg-red-100 text-red-800',

                            'high' =>
                                'bg-orange-100 text-orange-800',

                            'medium' =>
                                'bg-amber-100 text-amber-800',

                            'low' =>
                                'bg-blue-100 text-blue-800',

                            'positive' =>
                                'bg-emerald-100 text-emerald-800',

                            default =>
                                'bg-slate-100 text-slate-700',
                        };

                        $dotClasses = match ($event->severity) {
                            'critical' => 'bg-red-500',
                            'high' => 'bg-orange-500',
                            'medium' => 'bg-amber-500',
                            'low' => 'bg-blue-500',
                            'positive' => 'bg-emerald-500',
                            default => 'bg-slate-400',
                        };
                    @endphp

                    <article class="relative border-l-2 border-slate-200 pb-10 pl-8 last:pb-0">
                        <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full {{ $dotClasses }}"></span>

                        <div class="flex flex-wrap items-start justify-between gap-5">
                            <div class="max-w-3xl">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClasses }}">
                                        {{ str($event->severity)->title() }}
                                    </span>

                                    <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ str($event->category)
                                            ->replace('_', ' ')
                                            ->title() }}
                                    </span>

                                    <span class="text-xs text-slate-400">
                                        {{ $event->event_date->format(
                                            'M j, Y'
                                        ) }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-lg font-semibold text-slate-900">
                                    {{ $event->headline }}
                                </h3>

                                @if ($event->summary)
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ $event->summary }}
                                    </p>
                                @endif

                                @if (! empty($event->metrics))
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        @foreach ($event->metrics as $key => $value)
                                            @if ($value !== null)
                                                <span class="rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                                    {{ str($key)
                                                        ->replace('_', ' ')
                                                        ->title() }}:
                                                    <strong>
                                                        {{ is_float($value)
                                                            ? number_format($value, 2)
                                                            : $value }}
                                                    </strong>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if (
                                $event->route_name
                                && Route::has($event->route_name)
                            )
                                <a
                                    href="{{ $event->route_name === 'advisor-audit.history.show'
                                        && $event->source_id
                                            ? route(
                                                $event->route_name,
                                                $event->source_id
                                            )
                                            : route($event->route_name) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-500"
                                >
                                    Supporting analysis →
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="py-12 text-center">
                        <p class="font-semibold text-slate-900">
                            No timeline events yet
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            At least two recorded audits are required to detect changes.
                        </p>
                    </div>
                @endforelse
            </section>

            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>