<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Portfolio intelligence
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Monthly Portfolio Reviews
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Saved summaries of portfolio changes, risks and positive developments.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('monthly-reviews.generate') }}"
                class="flex flex-wrap gap-3"
            >
                @csrf

                <input
                    type="month"
                    name="month"
                    value="{{ now()->format('Y-m') }}"
                    class="rounded-xl border-slate-300"
                >

                <button
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                >
                    Generate review
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($reviews->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                    <h3 class="text-xl font-semibold text-slate-900">
                        No monthly reviews yet
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                        Generate a review after portfolio snapshots, audit runs
                        and timeline events have been recorded.
                    </p>
                </section>
            @else
                <section class="grid gap-6 lg:grid-cols-2">
                    @foreach ($reviews as $review)
                        <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-blue-600">
                                        {{ $review->period_start->format('F Y') }}
                                    </p>

                                    <h3 class="mt-2 text-xl font-semibold text-slate-900">
                                        {{ $review->headline }}
                                    </h3>
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ str($review->status)->title() }}
                                </span>
                            </div>

                            <p class="mt-4 line-clamp-4 text-sm leading-7 text-slate-600">
                                {{ $review->summary }}
                            </p>

                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs text-slate-500">
                                        Events
                                    </p>

                                    <p class="mt-2 text-xl font-semibold text-slate-900">
                                        {{ $review->event_count }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <p class="text-xs text-emerald-700">
                                        Positive
                                    </p>

                                    <p class="mt-2 text-xl font-semibold text-emerald-900">
                                        {{ $review->positive_event_count }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-amber-50 p-4">
                                    <p class="text-xs text-amber-700">
                                        Review
                                    </p>

                                    <p class="mt-2 text-xl font-semibold text-amber-900">
                                        {{ $review->attention_event_count }}
                                    </p>
                                </div>
                            </div>

                            <a
                                href="{{ route(
                                    'monthly-reviews.show',
                                    $review
                                ) }}"
                                class="mt-6 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                Open monthly review →
                            </a>
                        </article>
                    @endforeach
                </section>

                {{ $reviews->links() }}
            @endif
        </div>
    </div>
</x-app-layout>