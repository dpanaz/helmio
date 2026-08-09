<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Monthly portfolio intelligence
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Monthly Review
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    A saved summary of portfolio changes, score movement,
                    review items, positive developments, and limitations.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('monthly-reviews.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Review History
                </a>

                <a
                    href="{{ route(
                        'monthly-reviews.pdf',
                        $review
                    ) }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                >
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $valueChange =
            $review->portfolio_value_change;

        $scoreChange =
            $review->helm_score_change;

        $valueChangePositive =
            $valueChange !== null
            && $valueChange >= 0;

        $scoreChangePositive =
            $scoreChange !== null
            && $scoreChange >= 0;

        $reviewMonth =
            $review->review_month
                ? \Carbon\Carbon::parse(
                    $review->review_month
                )
                : $review->created_at;
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Executive summary --}}
            <section
                class="overflow-hidden rounded-3xl border border-blue-500/20 bg-slate-900 shadow-xl"
            >
                <div class="p-7 lg:p-9">
                    <div
                        class="flex flex-col gap-7 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div class="max-w-4xl">
                            <div
                                class="flex flex-wrap items-center gap-3"
                            >
                                <span
                                    class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                                >
                                    Monthly Intelligence Review
                                </span>

                                @if ($reviewMonth)
                                    <span
                                        class="text-xs text-slate-600"
                                    >
                                        {{ $reviewMonth->format(
                                            'F Y'
                                        ) }}
                                    </span>
                                @endif
                            </div>

                            <h3
                                class="mt-5 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                {{ $review->headline }}
                            </h3>

                            <p
                                class="mt-5 text-base leading-8 text-slate-300"
                            >
                                {{ $review->summary }}
                            </p>
                        </div>

                        <div
                            class="shrink-0 rounded-2xl border border-slate-800 bg-slate-950 p-5 lg:w-52"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                            >
                                Saved Review
                            </p>

                            <p
                                class="mt-2 font-semibold text-white"
                            >
                                {{ $review->created_at
                                    ?->format('M j, Y') }}
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{ $review->created_at
                                    ?->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Metrics --}}
            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Ending value
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold tracking-tight text-white"
                    >
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

                <article
                    @class([
                        'rounded-2xl border p-6 shadow-xl',

                        'border-emerald-500/20 bg-emerald-500/[0.05]' =>
                            $valueChangePositive,

                        'border-red-500/20 bg-red-500/[0.05]' =>
                            $valueChange !== null
                            && ! $valueChangePositive,

                        'border-slate-800 bg-slate-900' =>
                            $valueChange === null,
                    ])
                >
                    <p
                        @class([
                            'text-sm',

                            'text-emerald-300' =>
                                $valueChangePositive,

                            'text-red-300' =>
                                $valueChange !== null
                                && ! $valueChangePositive,

                            'text-slate-500' =>
                                $valueChange === null,
                        ])
                    >
                        Value change
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold tracking-tight text-white"
                    >
                        @if ($valueChange !== null)
                            {{ $valueChange >= 0 ? '+' : '-' }}
                            ${{ number_format(
                                abs($valueChange),
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6 shadow-xl"
                >
                    <p class="text-sm text-blue-300">
                        Helm Score
                    </p>

                    <div class="mt-3 flex items-end gap-2">
                        <p
                            class="text-2xl font-semibold tracking-tight text-white"
                        >
                            {{ $review->ending_helm_score ?? '—' }}
                        </p>

                        @if ($scoreChange !== null)
                            <span
                                @class([
                                    'pb-0.5 text-sm font-semibold',

                                    'text-emerald-300' =>
                                        $scoreChangePositive,

                                    'text-red-300' =>
                                        ! $scoreChangePositive,
                                ])
                            >
                                {{ $scoreChange >= 0 ? '+' : '' }}
                                {{ $scoreChange }}
                            </span>
                        @endif
                    </div>

                    @if ($scoreChange !== null)
                        <p class="mt-2 text-xs text-slate-600">
                            Score change this month
                        </p>
                    @endif
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Advisor Audit grade
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold tracking-tight text-white"
                    >
                        {{ $review->ending_audit_grade ?? '—' }}
                    </p>
                </article>
            </section>

            {{-- Key changes and review items --}}
            <section class="grid gap-6 xl:grid-cols-2">
                <article
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Portfolio movement
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Key Changes
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Material changes detected during the review period.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($review->key_changes ?? [] as $change)
                            @php
                                $severity =
                                    $change['severity']
                                    ?? 'information';

                                $severityClasses =
                                    match ($severity) {
                                        'critical' =>
                                            'border-red-500/20 bg-red-500/10 text-red-300',

                                        'high' =>
                                            'border-orange-500/20 bg-orange-500/10 text-orange-300',

                                        'medium',
                                        'moderate' =>
                                            'border-amber-500/20 bg-amber-500/10 text-amber-300',

                                        'positive' =>
                                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                        default =>
                                            'border-blue-500/20 bg-blue-500/10 text-blue-300',
                                    };
                            @endphp

                            <div
                                class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                            >
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        class="rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClasses }}"
                                    >
                                        {{ str($severity)->title() }}
                                    </span>

                                    @if (! empty($change['category']))
                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            {{ str($change['category'])
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </span>
                                    @endif
                                </div>

                                <h4
                                    class="mt-4 font-semibold text-white"
                                >
                                    {{ $change['headline']
                                        ?? 'Portfolio change' }}
                                </h4>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-400"
                                >
                                    {{ $change['summary']
                                        ?? 'No additional description was recorded.' }}
                                </p>
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-6 text-center"
                            >
                                <p
                                    class="text-sm text-slate-500"
                                >
                                    No material changes were detected.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                        >
                            Attention recommended
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Items to Review
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Issues that may deserve additional review or discussion.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($review->review_items ?? [] as $item)
                            <div
                                class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-5"
                            >
                                <div
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-300"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 9v4m0 4h.01"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p
                                            class="font-semibold text-amber-200"
                                        >
                                            {{ $item['headline']
                                                ?? 'Review item' }}
                                        </p>

                                        <p
                                            class="mt-2 text-sm leading-6 text-slate-400"
                                        >
                                            {{ $item['summary']
                                                ?? 'Review the supporting portfolio analysis.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-6 text-center"
                            >
                                <p
                                    class="text-sm font-medium text-emerald-300"
                                >
                                    No high-priority review items were detected.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            {{-- Positive developments --}}
            @if (! empty($review->positive_changes))
                <section
                    class="rounded-3xl border border-emerald-500/20 bg-emerald-500/[0.05] p-7"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-300"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400"
                            >
                                Positive signals
                            </p>

                            <h3
                                class="mt-1 font-semibold text-white"
                            >
                                Positive Developments
                            </h3>
                        </div>
                    </div>

                    <div
                        class="mt-5 grid gap-3 md:grid-cols-2"
                    >
                        @foreach ($review->positive_changes as $change)
                            <div
                                class="flex gap-3 rounded-2xl border border-emerald-500/10 bg-slate-950/40 p-4"
                            >
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>

                                <p
                                    class="text-sm leading-6 text-slate-300"
                                >
                                    {{ is_array($change)
                                        ? (
                                            $change['summary']
                                            ?? $change['message']
                                            ?? $change['headline']
                                            ?? 'Positive portfolio development detected.'
                                        )
                                        : $change }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Limitations --}}
            @if (! empty($review->limitations))
                <section
                    class="rounded-3xl border border-amber-500/20 bg-amber-500/[0.05] p-7"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                        >
                            Keep in mind
                        </p>

                        <h3
                            class="mt-2 font-semibold text-white"
                        >
                            Review Limitations
                        </h3>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Some conclusions may be limited by incomplete
                            account history or portfolio data.
                        </p>
                    </div>

                    <div
                        class="mt-5 grid gap-3 md:grid-cols-2"
                    >
                        @foreach ($review->limitations as $limitation)
                            <div
                                class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4"
                            >
                                <p
                                    class="text-sm leading-6 text-slate-400"
                                >
                                    {{ is_array($limitation)
                                        ? (
                                            $limitation['message']
                                            ?? $limitation['summary']
                                            ?? $limitation['title']
                                            ?? 'Some portfolio data may be incomplete.'
                                        )
                                        : $limitation }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Bottom actions --}}
            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h3
                            class="font-semibold text-white"
                        >
                            Continue your review
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Compare prior months or download this review.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('monthly-reviews.index') }}"
                            class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                        >
                            Review History
                        </a>

                        <a
                            href="{{ route(
                                'monthly-reviews.pdf',
                                $review
                            ) }}"
                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Download PDF
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>