@php
    $priorityItems = collect();

    if ($topConcern) {
        $priorityItems->push([
            'tone' => 'critical',
            'eyebrow' => 'Top concern',
            'title' => data_get(
                $topConcern,
                'title',
                'Advisor audit finding'
            ),
            'message' => data_get(
                $topConcern,
                'message',
                'A portfolio issue needs your attention.'
            ),
            'route' => route(
                'advisor-action-center.index'
            ),
            'action' => 'Review finding',
        ]);
    }

    if (
        ! $topConcern
        && $topOpportunity
    ) {
        $priorityItems->push([
            'tone' => 'opportunity',
            'eyebrow' => 'Top opportunity',
            'title' => data_get(
                $topOpportunity,
                'title',
                'Portfolio opportunity'
            ),
            'message' => data_get(
                $topOpportunity,
                'message',
                'Helmio found an opportunity worth reviewing.'
            ),
            'route' => route(
                'advisor-action-center.index'
            ),
            'action' => 'Review opportunity',
        ]);
    }

    if ($profileCompleteness < 1) {
        $priorityItems->push([
            'tone' => 'profile',
            'eyebrow' => 'Investor profile',
            'title' => 'Complete your investor profile',
            'message' => sprintf(
                'Your profile is %d%% complete. More detail improves suitability and risk analysis.',
                (int) round(
                    $profileCompleteness * 100
                )
            ),
            'route' => route(
                'investor-profile.edit'
            ),
            'action' => 'Update profile',
        ]);
    }

    if ($latestAiInsightIsStale) {
        $priorityItems->push([
            'tone' => 'ai',
            'eyebrow' => 'AI insight',
            'title' => 'Your portfolio insight is updating',
            'message' => data_get(
                $latestAiInsight,
                'stale_reason',
                'Portfolio data changed after the latest insight was generated.'
            ),
            'route' => route(
                'ai-insights.index'
            ),
            'action' => 'View status',
        ]);
    }

    if (
        $priorityItems->count() < 3
        && $topOpportunity
    ) {
        $priorityItems->push([
            'tone' => 'opportunity',
            'eyebrow' => 'Opportunity',
            'title' => data_get(
                $topOpportunity,
                'title',
                'Portfolio opportunity'
            ),
            'message' => data_get(
                $topOpportunity,
                'message',
                'Helmio found an opportunity worth reviewing.'
            ),
            'route' => route(
                'advisor-action-center.index'
            ),
            'action' => 'Review opportunity',
        ]);
    }

    $priorityItems = $priorityItems
        ->unique('title')
        ->take(3)
        ->values();

    $priorityToneClasses = [
        'critical' => [
            'card' =>
                'border-red-200 bg-red-50',
            'icon' =>
                'bg-red-100 text-red-700',
            'eyebrow' =>
                'text-red-700',
            'title' =>
                'text-red-950',
            'message' =>
                'text-red-800',
            'link' =>
                'text-red-700 hover:text-red-600',
        ],

        'profile' => [
            'card' =>
                'border-blue-200 bg-blue-50',
            'icon' =>
                'bg-blue-100 text-blue-700',
            'eyebrow' =>
                'text-blue-700',
            'title' =>
                'text-blue-950',
            'message' =>
                'text-blue-800',
            'link' =>
                'text-blue-700 hover:text-blue-600',
        ],

        'ai' => [
            'card' =>
                'border-violet-200 bg-violet-50',
            'icon' =>
                'bg-violet-100 text-violet-700',
            'eyebrow' =>
                'text-violet-700',
            'title' =>
                'text-violet-950',
            'message' =>
                'text-violet-800',
            'link' =>
                'text-violet-700 hover:text-violet-600',
        ],

        'opportunity' => [
            'card' =>
                'border-emerald-200 bg-emerald-50',
            'icon' =>
                'bg-emerald-100 text-emerald-700',
            'eyebrow' =>
                'text-emerald-700',
            'title' =>
                'text-emerald-950',
            'message' =>
                'text-emerald-800',
            'link' =>
                'text-emerald-700 hover:text-emerald-600',
        ],
    ];
@endphp

<section
    class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6"
>
    <div
        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
    >
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600"
            >
                Action Center
            </p>

            <h2
                class="mt-2 text-xl font-semibold tracking-tight text-slate-950"
            >
                Today’s Priorities
            </h2>

            <p
                class="mt-1 text-sm leading-6 text-slate-500"
            >
                The most important items Helmio recommends reviewing next.
            </p>
        </div>

        <a
            href="{{ route('advisor-action-center.index') }}"
            class="inline-flex text-sm font-semibold text-slate-700 hover:text-slate-950"
        >
            View all actions →
        </a>
    </div>

    @if ($priorityItems->isEmpty())
        <div
            class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5"
        >
            <div class="flex items-start gap-4">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
                >
                    <svg
                        class="h-6 w-6"
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
                        class="font-semibold text-emerald-950"
                    >
                        You’re all caught up
                    </p>

                    <p
                        class="mt-1 text-sm leading-6 text-emerald-800"
                    >
                        Helmio did not find any urgent actions in the available portfolio data.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div
            class="mt-5 grid gap-4 lg:grid-cols-3"
        >
            @foreach ($priorityItems as $item)
                @php
                    $classes =
                        $priorityToneClasses[
                            $item['tone']
                        ]
                        ?? $priorityToneClasses[
                            'profile'
                        ];
                @endphp

                <article
                    class="min-w-0 rounded-2xl border p-5 {{ $classes['card'] }}"
                >
                    <div
                        class="flex items-start gap-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $classes['icon'] }}"
                        >
                            @if ($item['tone'] === 'critical')
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
                                        d="M12 9v4m0 4h.01M10.3 3.7 2.6 17a2 2 0 0 0 1.73 3h15.34A2 2 0 0 0 21.4 17L13.7 3.7a2 2 0 0 0-3.4 0Z"
                                    />
                                </svg>
                            @elseif ($item['tone'] === 'ai')
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
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                    />
                                </svg>
                            @elseif ($item['tone'] === 'opportunity')
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
                            @else
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
                                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                                    />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-xs font-semibold uppercase tracking-wide {{ $classes['eyebrow'] }}"
                            >
                                {{ $item['eyebrow'] }}
                            </p>

                            <h3
                                class="mt-2 break-words font-semibold {{ $classes['title'] }}"
                            >
                                {{ $item['title'] }}
                            </h3>

                            <p
                                class="mt-2 break-words text-sm leading-6 {{ $classes['message'] }}"
                            >
                                {{ $item['message'] }}
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ $item['route'] }}"
                        class="mt-5 inline-flex text-sm font-semibold {{ $classes['link'] }}"
                    >
                        {{ $item['action'] }} →
                    </a>
                </article>
            @endforeach
        </div>
    @endif
</section>