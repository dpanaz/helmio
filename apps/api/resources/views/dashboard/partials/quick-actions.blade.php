@php
    $quickActions = [
        [
            'label' => 'Connect',
            'description' => 'Add an investment account',
            'route' => 'accounts.create',
            'tone' => 'bg-blue-50 text-blue-700 ring-blue-100',
            'icon' => 'plus',
        ],
        [
            'label' => 'Run Audit',
            'description' => 'Review advisor performance',
            'route' => 'advisor-audit.index',
            'tone' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
            'icon' => 'shield',
        ],
        [
            'label' => 'Ask Helmio',
            'description' => 'Ask about your portfolio',
            'route' => 'ask-helmio.index',
            'tone' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon' => 'sparkles',
        ],
        [
            'label' => 'Performance',
            'description' => 'Review returns and benchmarks',
            'route' => 'analytics.performance',
            'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon' => 'chart',
        ],
        [
            'label' => 'Profile',
            'description' => 'Update suitability details',
            'route' => 'investor-profile.edit',
            'tone' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'icon' => 'user',
        ],
        [
            'label' => 'Action Center',
            'description' => 'Review prioritized findings',
            'route' => 'advisor-action-center.index',
            'tone' => 'bg-rose-50 text-rose-700 ring-rose-100',
            'icon' => 'flag',
        ],
    ];
@endphp

<section>
    <div
        class="flex items-end justify-between gap-4"
    >
        <div>
            <h2
                class="text-lg font-semibold text-slate-950"
            >
                Quick Actions
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Jump directly to Helmio’s most-used tools.
            </p>
        </div>
    </div>

    <div
        class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6"
    >
        @foreach ($quickActions as $action)
            <a
                href="{{ route($action['route']) }}"
                class="group min-w-0 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl ring-1 {{ $action['tone'] }}"
                >
                    @if ($action['icon'] === 'plus')
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
                                d="M12 5v14M5 12h14"
                            />
                        </svg>
                    @elseif ($action['icon'] === 'shield')
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
                                d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Z"
                            />
                        </svg>
                    @elseif ($action['icon'] === 'sparkles')
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
                    @elseif ($action['icon'] === 'chart')
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
                                d="M4.5 19.5v-6m5.25 6V9m5.25 10.5V5.25m5.25 14.25H3.75"
                            />
                        </svg>
                    @elseif ($action['icon'] === 'user')
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
                                d="M6 4.5h9l3 3v12H6v-15Zm3 5.25h6m-6 3h6"
                            />
                        </svg>
                    @endif
                </div>

                <p
                    class="mt-4 truncate font-semibold text-slate-950"
                >
                    {{ $action['label'] }}
                </p>

                <p
                    class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"
                >
                    {{ $action['description'] }}
                </p>
            </a>
        @endforeach
    </div>
</section>