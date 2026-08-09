<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Portfolio alerts
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Notifications
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Review portfolio changes, Advisor Audit alerts,
                    score movements, and other events that may deserve
                    your attention.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-blue-500/50 hover:text-white"
                    >
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Summary --}}
            <section
                class="grid gap-4 sm:grid-cols-2"
            >
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Total notifications
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        @if (method_exists($notifications, 'total'))
                            {{ number_format($notifications->total()) }}
                        @else
                            {{ number_format($notifications->count()) }}
                        @endif
                    </p>
                </article>

                <article
                    @class([
                        'rounded-2xl border p-6 shadow-xl',

                        'border-blue-500/20 bg-blue-500/[0.06]' =>
                            $unreadCount > 0,

                        'border-slate-800 bg-slate-900' =>
                            $unreadCount === 0,
                    ])
                >
                    <p
                        @class([
                            'text-sm',
                            'text-blue-300' => $unreadCount > 0,
                            'text-slate-500' => $unreadCount === 0,
                        ])
                    >
                        Unread
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        {{ number_format($unreadCount) }}
                    </p>
                </article>
            </section>

            {{-- Notification activity --}}
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
                            <h3 class="text-lg font-semibold text-white">
                                Activity
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $unreadCount }}
                                unread notification{{ $unreadCount === 1 ? '' : 's' }}
                            </p>
                        </div>

                        @if ($unreadCount === 0)
                            <span
                                class="inline-flex w-fit rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                            >
                                All caught up
                            </span>
                        @endif
                    </div>
                </div>

                <div class="divide-y divide-slate-800">
                    @forelse ($notifications as $notification)
                        @php
                            $data =
                                $notification->data;

                            $severity =
                                $data['severity']
                                ?? 'information';

                            $severityClasses = match ($severity) {
                                'critical' =>
                                    'border-red-500/20 bg-red-500/10 text-red-300',

                                'high' =>
                                    'border-orange-500/20 bg-orange-500/10 text-orange-300',

                                'medium',
                                'moderate' =>
                                    'border-amber-500/20 bg-amber-500/10 text-amber-300',

                                'low' =>
                                    'border-blue-500/20 bg-blue-500/10 text-blue-300',

                                'positive' =>
                                    'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                default =>
                                    'border-slate-700 bg-slate-800 text-slate-400',
                            };

                            $dotClasses =
                                $notification->unread()
                                    ? 'bg-blue-400 ring-blue-500/20'
                                    : 'bg-slate-600 ring-slate-700/20';
                        @endphp

                        <article
                            @class([
                                'relative p-6 transition sm:p-7',

                                'bg-blue-500/[0.025]' =>
                                    $notification->unread(),

                                'hover:bg-slate-800/20' =>
                                    $notification->read(),
                            ])
                        >
                            <div
                                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div
                                    class="flex max-w-3xl gap-4"
                                >
                                    {{-- Read/unread marker --}}
                                    <div class="pt-2">
                                        <span
                                            class="block h-3 w-3 rounded-full ring-4 {{ $dotClasses }}"
                                        ></span>
                                    </div>

                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClasses }}"
                                            >
                                                {{ str($severity)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            <span
                                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                            >
                                                {{ str(
                                                    $data['category']
                                                    ?? 'audit'
                                                )
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            @if ($notification->unread())
                                                <span
                                                    class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                                                >
                                                    New
                                                </span>
                                            @endif
                                        </div>

                                        <h4
                                            class="mt-4 text-base font-semibold text-white"
                                        >
                                            {{ $data['title']
                                                ?? 'Portfolio update' }}
                                        </h4>

                                        <p
                                            class="mt-2 text-sm leading-7 text-slate-400"
                                        >
                                            {{ $data['message']
                                                ?? 'A portfolio change was detected.' }}
                                        </p>

                                        <div
                                            class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2"
                                        >
                                            <p
                                                class="text-xs text-slate-600"
                                            >
                                                {{ $notification
                                                    ->created_at
                                                    ->diffForHumans() }}
                                            </p>

                                            @if (! empty($data['financial_impact']))
                                                <span
                                                    class="text-xs font-semibold text-slate-400"
                                                >
                                                    Estimated impact:
                                                    {{ money(
                                                        $data['financial_impact'],
                                                        0
                                                    ) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div
                                    class="flex shrink-0 flex-wrap gap-2 lg:justify-end"
                                >
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'notifications.read',
                                            $notification
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                                        >
                                            {{ $data['action_label']
                                                ?? 'Review' }}

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
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'notifications.destroy',
                                            $notification
                                        ) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-400 transition hover:border-red-500/40 hover:text-red-300"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </div>
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
                                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                                    />
                                </svg>
                            </div>

                            <h3
                                class="mt-5 text-lg font-semibold text-white"
                            >
                                No notifications yet
                            </h3>

                            <p
                                class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500"
                            >
                                Advisor Audit changes, portfolio alerts,
                                monthly review events, and other important
                                updates will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Pagination --}}
            @if (
                method_exists($notifications, 'hasPages')
                && $notifications->hasPages()
            )
                <div class="text-slate-400">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>