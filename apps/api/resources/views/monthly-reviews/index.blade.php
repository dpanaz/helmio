<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Portfolio intelligence
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Monthly Reviews
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Saved month-by-month summaries of portfolio changes,
                    risk, performance, Advisor Audit results, and items
                    that deserve attention.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('monthly-reviews.generate') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
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
                            d="M12 4.5v15m7.5-7.5h-15"
                        />
                    </svg>

                    Generate Review
                </button>
            </form>
        </div>
    </x-slot>

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

            @if ($errors->any())
                <div
                    class="rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5"
                >
                    <h3 class="font-semibold text-red-300">
                        Unable to generate the monthly review
                    </h3>

                    <div class="mt-3 space-y-1 text-sm text-slate-400">
                        @foreach ($errors->all() as $error)
                            <p>• {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Intro --}}
            <section
                class="overflow-hidden rounded-3xl border border-blue-500/20 bg-slate-900 shadow-xl"
            >
                <div
                    class="grid gap-8 p-7 lg:grid-cols-[1.5fr_1fr] lg:p-9"
                >
                    <div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3.75 9.75h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Monthly intelligence
                        </p>

                        <h3
                            class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-white"
                        >
                            See what changed — without having to dig
                            through every account.
                        </h3>

                        <p
                            class="mt-4 max-w-3xl text-sm leading-7 text-slate-400"
                        >
                            Each monthly review summarizes portfolio value,
                            Helm Score movement, Advisor Audit results,
                            important changes, positive developments, and
                            issues that may deserve closer review.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-6"
                    >
                        <p
                            class="text-sm font-semibold text-white"
                        >
                            What each review includes
                        </p>

                        <div class="mt-5 space-y-4">
                            @foreach ([
                                'Portfolio value and monthly change',
                                'Helm Score movement',
                                'Advisor Audit grade',
                                'Material portfolio changes',
                                'Items that deserve review',
                                'Positive developments and limitations',
                            ] as $item)
                                <div class="flex items-start gap-3">
                                    <svg
                                        class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400"
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
                                        class="text-sm leading-6 text-slate-400"
                                    >
                                        {{ $item }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- Review history --}}
            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div
                    class="border-b border-slate-800 px-6 py-5 sm:px-8"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3
                                class="text-lg font-semibold text-white"
                            >
                                Review history
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Previously generated monthly portfolio reviews.
                            </p>
                        </div>

                        @if (method_exists($reviews, 'total'))
                            <span
                                class="text-xs font-medium text-slate-600"
                            >
                                {{ number_format($reviews->total()) }}
                                {{ \Illuminate\Support\Str::plural(
                                    'review',
                                    $reviews->total()
                                ) }}
                            </span>
                        @else
                            <span
                                class="text-xs font-medium text-slate-600"
                            >
                                {{ number_format($reviews->count()) }}
                                {{ \Illuminate\Support\Str::plural(
                                    'review',
                                    $reviews->count()
                                ) }}
                            </span>
                        @endif
                    </div>
                </div>

                @forelse ($reviews as $review)
                    @php
                        $valueChange =
                            $review->portfolio_value_change;

                        $scoreChange =
                            $review->helm_score_change;

                        $valueChangeClass =
                            $valueChange === null
                                ? 'text-slate-400'
                                : (
                                    $valueChange >= 0
                                        ? 'text-emerald-300'
                                        : 'text-red-300'
                                );

                        $scoreChangeClass =
                            $scoreChange === null
                                ? 'text-slate-500'
                                : (
                                    $scoreChange >= 0
                                        ? 'text-emerald-300'
                                        : 'text-red-300'
                                );
                    @endphp

                    <article
                        class="border-b border-slate-800 p-6 transition last:border-b-0 hover:bg-slate-800/25 sm:p-8"
                    >
                        <div
                            class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                        >
                            <div class="max-w-3xl">
                                <div
                                    class="flex flex-wrap items-center gap-3"
                                >
                                    <span
                                        class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                                    >
                                        Monthly Review
                                    </span>

                                    @if ($review->review_month)
                                        <span
                                            class="text-xs text-slate-600"
                                        >
                                            {{ \Carbon\Carbon::parse(
                                                $review->review_month
                                            )->format('F Y') }}
                                        </span>
                                    @elseif ($review->created_at)
                                        <span
                                            class="text-xs text-slate-600"
                                        >
                                            {{ $review->created_at->format(
                                                'F Y'
                                            ) }}
                                        </span>
                                    @endif
                                </div>

                                <h4
                                    class="mt-4 text-xl font-semibold text-white"
                                >
                                    {{ $review->headline
                                        ?: 'Monthly portfolio review' }}
                                </h4>

                                <p
                                    class="mt-3 line-clamp-3 text-sm leading-7 text-slate-400"
                                >
                                    {{ $review->summary
                                        ?: 'No review summary is available.' }}
                                </p>
                            </div>

                            <div
                                class="grid shrink-0 grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[31rem]"
                            >
                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                >
                                    <p
                                        class="text-xs text-slate-600"
                                    >
                                        Ending value
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold text-white"
                                    >
                                        @if ($review->ending_portfolio_value !== null)
                                            ${{ number_format(
                                                $review->ending_portfolio_value,
                                                0
                                            ) }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                >
                                    <p
                                        class="text-xs text-slate-600"
                                    >
                                        Value change
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold {{ $valueChangeClass }}"
                                    >
                                        @if ($valueChange !== null)
                                            {{ $valueChange >= 0 ? '+' : '-' }}
                                            ${{ number_format(
                                                abs($valueChange),
                                                0
                                            ) }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                >
                                    <p
                                        class="text-xs text-slate-600"
                                    >
                                        Helm Score
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold text-white"
                                    >
                                        {{ $review->ending_helm_score ?? '—' }}
                                    </p>

                                    @if ($scoreChange !== null)
                                        <p
                                            class="mt-1 text-xs {{ $scoreChangeClass }}"
                                        >
                                            {{ $scoreChange >= 0 ? '+' : '' }}
                                            {{ $scoreChange }}
                                        </p>
                                    @endif
                                </div>

                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                >
                                    <p
                                        class="text-xs text-slate-600"
                                    >
                                        Audit grade
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-semibold text-white"
                                    >
                                        {{ $review->ending_audit_grade ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5"
                        >
                            <a
                                href="{{ route(
                                    'monthly-reviews.show',
                                    $review
                                ) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                            >
                                Open Review

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
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </a>

                            <a
                                href="{{ route(
                                    'monthly-reviews.pdf',
                                    $review
                                ) }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                            >
                                Download PDF
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-slate-500"
                        >
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3.75 9.75h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z"
                                />
                            </svg>
                        </div>

                        <h3
                            class="mt-5 text-lg font-semibold text-white"
                        >
                            No monthly reviews yet
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500"
                        >
                            Generate your first review after Helmio has
                            enough portfolio history to compare one period
                            with another.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('monthly-reviews.generate') }}"
                            class="mt-6"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                            >
                                Generate First Review
                            </button>
                        </form>
                    </div>
                @endforelse
            </section>

            @if (
                method_exists($reviews, 'hasPages')
                && $reviews->hasPages()
            )
                <div class="text-slate-400">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>