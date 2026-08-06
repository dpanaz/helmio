<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-3">
    <a
        href="{{ route(
            'monthly-reviews.pdf',
            $review
        ) }}"
        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
    >
        Download PDF
    </a>

    <a
        href="{{ route('monthly-reviews.index') }}"
        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
    >
        Review history
    </a>
</div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl lg:p-10">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-300">
                    Monthly intelligence review
                </p>

                <h3 class="mt-4 text-3xl font-semibold tracking-tight">
                    {{ $review->headline }}
                </h3>

                <p class="mt-5 max-w-4xl text-base leading-8 text-slate-300">
                    {{ $review->summary }}
                </p>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Ending value
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        @if ($review->ending_portfolio_value !== null)
                            ${{ number_format(
                                $review->ending_portfolio_value,
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Value change
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        @if ($review->portfolio_value_change !== null)
                            {{ $review->portfolio_value_change >= 0 ? '+' : '-' }}
                            ${{ number_format(
                                abs($review->portfolio_value_change),
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Audit score
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ $review->ending_helm_score ?? '—' }}
                    </p>

                    @if ($review->helm_score_change !== null)
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $review->helm_score_change >= 0 ? '+' : '' }}
                            {{ $review->helm_score_change }}
                            this month
                        </p>
                    @endif
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Audit grade
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ $review->ending_audit_grade ?? '—' }}
                    </p>
                </article>
            </section>

            <section class="grid gap-8 xl:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Key changes
                    </h3>

                    <div class="mt-6 space-y-4">
                        @forelse ($review->key_changes ?? [] as $change)
                            <div class="rounded-2xl border border-slate-200 p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ str($change['severity'])->title() }}
                                    </span>

                                    <span class="text-xs uppercase tracking-wide text-slate-400">
                                        {{ str($change['category'])
                                            ->replace('_', ' ')
                                            ->title() }}
                                    </span>
                                </div>

                                <h4 class="mt-3 font-semibold text-slate-900">
                                    {{ $change['headline'] }}
                                </h4>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $change['summary'] }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No material changes were detected.
                            </p>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Items to review
                    </h3>

                    <div class="mt-6 space-y-4">
                        @forelse ($review->review_items ?? [] as $item)
                            <div class="rounded-2xl bg-amber-50 p-5">
                                <p class="font-semibold text-amber-950">
                                    {{ $item['headline'] }}
                                </p>

                                <p class="mt-2 text-sm leading-6 text-amber-900">
                                    {{ $item['summary'] }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No high-priority review items were detected.
                            </p>
                        @endforelse
                    </div>
                </article>
            </section>

            @if (! empty($review->positive_changes))
                <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-7">
                    <h3 class="font-semibold text-emerald-950">
                        Positive developments
                    </h3>

                    <div class="mt-4 space-y-3">
                        @foreach ($review->positive_changes as $change)
                            <p class="text-sm leading-6 text-emerald-900">
                                ✓ {{ $change }}
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (! empty($review->limitations))
                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-7">
                    <h3 class="font-semibold text-amber-950">
                        Review limitations
                    </h3>

                    <div class="mt-4 space-y-3">
                        @foreach ($review->limitations as $limitation)
                            <p class="text-sm leading-6 text-amber-900">
                                {{ $limitation }}
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>