<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Recorded audit
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ $run->calculated_for_date->format('F j, Y') }}
                </h2>
            </div>

            <a
                href="{{ route('advisor-audit.history') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Back to history
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-3xl bg-slate-950 p-8 text-white xl:col-span-2">
                    <p class="text-sm font-medium text-blue-300">
                        Advisor Audit Grade
                    </p>

                    <div class="mt-4 flex items-end gap-5">
                        <span class="text-7xl font-semibold">
                            {{ $run->audit_grade }}
                        </span>

                        <div class="pb-2">
                            <p class="text-xl font-semibold">
                                {{ $run->audit_score ?? '—' }}
                                @if ($run->audit_score !== null)
                                    / 100
                                @endif
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $run->audit_label }}
                            </p>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-8">
                    <p class="text-sm text-slate-500">
                        Portfolio value
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $run->portfolio_value,
                            2
                        ) }}
                    </p>

                    <p class="mt-5 text-sm text-slate-500">
                        {{ $run->issue_count }} review items
                    </p>
                </article>
            </section>

            @if ($comparison['has_previous'])
                <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        [
                            'New',
                            $comparison['new_findings']->count(),
                        ],
                        [
                            'Improved',
                            $comparison['improved_findings']->count(),
                        ],
                        [
                            'Worsened',
                            $comparison['worsened_findings']->count(),
                        ],
                        [
                            'Resolved',
                            $comparison['resolved_findings']->count(),
                        ],
                    ] as [$label, $value])
                        <article class="rounded-2xl border border-slate-200 bg-white p-6">
                            <p class="text-sm text-slate-500">
                                {{ $label }}
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-slate-900">
                                {{ $value }}
                            </p>
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Recorded findings
                    </h3>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($run->findings as $finding)
                        <article class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-5">
                                <div class="max-w-4xl">
                                    <div class="flex gap-2">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ str($finding->severity)->title() }}
                                        </span>

                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                            {{ str($finding->status)->title() }}
                                        </span>
                                    </div>

                                    <h4 class="mt-4 font-semibold text-slate-900">
                                        {{ $finding->title }}
                                    </h4>

                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ $finding->description }}
                                    </p>

                                    @if ($finding->recommendation)
                                        <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                                            <p class="text-sm leading-6 text-slate-700">
                                                {{ $finding->recommendation }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                @if ($finding->score !== null)
                                    <p class="text-2xl font-semibold text-slate-900">
                                        {{ $finding->score }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">
                            No findings were stored for this audit.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
