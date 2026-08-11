<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                    Monitoring
                </p>

                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Notifications
                </h2>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                    Important portfolio changes, Advisor Audit findings,
                    score movements, and monitoring events.
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
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-blue-500/50 hover:bg-slate-800 hover:text-white"
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
                                d="m4.5 12.75 6 6 9-13.5"
                            />
                        </svg>

                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    @php
        $totalNotifications = method_exists($notifications, 'total')
            ? $notifications->total()
            : $notifications->count();

        $readCount = max(
            0,
            $totalNotifications - $unreadCount
        );
    @endphp

    <div
        class="min-h-screen bg-slate-950 py-8"
        data-helmio-unread-count="{{ $unreadCount }}"
    >
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Success message --}}
            @if (session('success'))
                <div
                    class="flex items-start gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-300"
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
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <p class="pt-1 text-sm font-medium text-emerald-300">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            {{-- Push notification controls --}}
            <section
                id="helmio-push-alerts"
                class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl sm:p-8"
            >
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="max-w-3xl">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                            >
                                <svg
                                    class="h-5 w-5"
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

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400"
                                >
                                    Device alerts
                                </p>

                                <h3
                                    class="mt-1 text-lg font-semibold text-white"
                                >
                                    Helmio push notifications
                                </h3>
                            </div>
                        </div>

                        <p
                            class="mt-4 text-sm leading-7 text-slate-400"
                        >
                            Enable alerts on this device so Helmio can notify you
                            about important portfolio changes, score movements,
                            high-priority findings, and monitoring events.
                        </p>

                        <p
                            id="helmio-alert-status"
                            class="mt-3 text-sm text-slate-500"
                            aria-live="polite"
                        >
                            Checking notification status...
                        </p>
                    </div>

                    <div
                        class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row"
                    >
                        <button
                            type="button"
                            id="enable-helmio-alerts"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
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
                                    d="M12 18.75a6 6 0 0 0 6-6V9A6 6 0 0 0 6 9v3.75a6 6 0 0 0 6 6Zm0 0v2.25m-3 0h6"
                                />
                            </svg>

                            Enable Helmio Alerts
                        </button>

                        <button
                            type="button"
                            id="disable-helmio-alerts"
                            class="hidden inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 text-sm font-semibold text-slate-400 transition hover:border-red-500/40 hover:bg-red-500/[0.05] hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Disable alerts
                        </button>
                    </div>
                </div>

                <div
                    id="helmio-ios-install-note"
                    class="mt-5 hidden rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] px-4 py-3 text-sm leading-6 text-amber-200"
                >
                    On iPhone or iPad, add Helmio to your Home Screen first,
                    then open the Home Screen app and enable alerts from there.
                </div>
            </section>

            {{-- Summary --}}
            <section class="grid gap-4 sm:grid-cols-3">

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                            >
                                Total activity
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold tracking-tight text-white"
                            >
                                {{ number_format($totalNotifications) }}
                            </p>
                        </div>

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-slate-400"
                        >
                            <svg
                                class="h-5 w-5"
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
                    </div>
                </article>

                <article
                    @class([
                        'rounded-2xl border p-6 shadow-xl',

                        'border-blue-500/30 bg-blue-500/[0.07]' =>
                            $unreadCount > 0,

                        'border-slate-800 bg-slate-900' =>
                            $unreadCount === 0,
                    ])
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p
                                @class([
                                    'text-xs font-semibold uppercase tracking-[0.14em]',

                                    'text-blue-300' =>
                                        $unreadCount > 0,

                                    'text-slate-500' =>
                                        $unreadCount === 0,
                                ])
                            >
                                Needs attention
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold tracking-tight text-white"
                            >
                                {{ number_format($unreadCount) }}
                            </p>
                        </div>

                        <div
                            @class([
                                'flex h-11 w-11 items-center justify-center rounded-xl border',

                                'border-blue-500/20 bg-blue-500/10 text-blue-300' =>
                                    $unreadCount > 0,

                                'border-slate-800 bg-slate-950 text-slate-500' =>
                                    $unreadCount === 0,
                            ])
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"
                                />
                            </svg>
                        </div>
                    </div>
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                            >
                                Reviewed
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold tracking-tight text-white"
                            >
                                {{ number_format($readCount) }}
                            </p>
                        </div>

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
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
                    </div>
                </article>

            </section>

            {{-- Activity --}}
            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div
                    class="border-b border-slate-800 px-6 py-5 sm:px-8"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400"
                            >
                                Monitoring activity
                            </p>

                            <h3 class="mt-1 text-lg font-semibold text-white">
                                Recent notifications
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                @if ($unreadCount > 0)
                                    {{ number_format($unreadCount) }}
                                    unread
                                    {{ Str::plural('notification', $unreadCount) }}
                                @else
                                    Nothing currently requires your attention.
                                @endif
                            </p>
                        </div>

                        @if ($unreadCount === 0)
                            <span
                                class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                                ></span>

                                All caught up
                            </span>
                        @else
                            <span
                                class="inline-flex w-fit items-center gap-2 rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-300"
                            >
                                <span
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-400"
                                ></span>

                                Monitoring active
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

                            $iconClasses = match ($severity) {
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

                        <article
                            @class([
                                'relative px-6 py-6 transition sm:px-8',

                                'bg-blue-500/[0.025]' =>
                                    $notification->unread(),

                                'hover:bg-slate-800/20',
                            ])
                        >
                            @if ($notification->unread())
                                <div
                                    class="absolute inset-y-0 left-0 w-0.5 bg-blue-500"
                                ></div>
                            @endif

                            <div
                                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div
                                    class="flex min-w-0 max-w-3xl items-start gap-4"
                                >
                                    {{-- Severity icon --}}
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $iconClasses }}"
                                    >
                                        @if ($severity === 'positive')
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
                                        @elseif (
                                            in_array(
                                                $severity,
                                                [
                                                    'critical',
                                                    'high',
                                                    'medium',
                                                    'moderate',
                                                ]
                                            )
                                        )
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
                                                    d="M11.25 11.25 12 6l.75 5.25L18 12l-5.25.75L12 18l-.75-5.25L6 12l5.25-.75Z"
                                                />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">

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
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-blue-500/20 bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-300"
                                                >
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full bg-blue-400"
                                                    ></span>

                                                    New
                                                </span>
                                            @endif
                                        </div>

                                        <h4
                                            class="mt-3 text-base font-semibold text-white"
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
                                            class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2"
                                        >
                                            <p class="text-xs text-slate-600">
                                                {{ $notification
                                                    ->created_at
                                                    ->diffForHumans() }}
                                            </p>

                                            @if (! empty($data['financial_impact']))
                                                <span
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400"
                                                >
                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6S9 7.007 9 8.25s1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25"
                                                        />
                                                    </svg>

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
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-500 transition hover:border-red-500/40 hover:bg-red-500/[0.05] hover:text-red-300"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>

                    @empty

                        <div class="px-6 py-16 text-center sm:px-8">

                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
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
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400"
                            >
                                Monitoring active
                            </p>

                            <h3
                                class="mt-2 text-xl font-semibold text-white"
                            >
                                Nothing needs your attention.
                            </h3>

                            <p
                                class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500"
                            >
                                Helmio is monitoring your portfolio.
                                Important Advisor Audit changes, score
                                movements, portfolio alerts, and review
                                events will appear here when detected.
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
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-4 text-slate-400">
                    {{ $notifications->links() }}
                </div>
            @endif

            {{-- Footer --}}
            <div
                class="flex flex-col gap-2 border-t border-slate-800 pt-5 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    Helmio continuously reviews supported portfolio data for meaningful changes.
                </p>

                <p>
                    Informational monitoring only — not investment advice.
                </p>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            async () => {
                const enableButton =
                    document.getElementById(
                        'enable-helmio-alerts'
                    );

                const disableButton =
                    document.getElementById(
                        'disable-helmio-alerts'
                    );

                const status =
                    document.getElementById(
                        'helmio-alert-status'
                    );

                const iosInstallNote =
                    document.getElementById(
                        'helmio-ios-install-note'
                    );

                if (
                    ! enableButton
                    || ! disableButton
                    || ! status
                ) {
                    return;
                }

                const isIos =
                    /iphone|ipad|ipod/i.test(
                        window.navigator.userAgent
                    );

                const isStandalone =
                    window.matchMedia(
                        '(display-mode: standalone)'
                    ).matches
                    || window.navigator.standalone === true;

                if (
                    isIos
                    && ! isStandalone
                    && iosInstallNote
                ) {
                    iosInstallNote.classList.remove(
                        'hidden'
                    );
                }

                if (! window.HelmioPush) {
                    status.textContent =
                        'Push notifications are not available yet on this device.';

                    enableButton.disabled = true;

                    return;
                }

                const refreshState =
                    async () => {
                        try {
                            const subscribed =
                                await window
                                    .HelmioPush
                                    .isSubscribed();

                            if (subscribed) {
                                enableButton.textContent =
                                    'Helmio Alerts Enabled';

                                enableButton.disabled =
                                    true;

                                disableButton.classList.remove(
                                    'hidden'
                                );

                                status.textContent =
                                    'This device is subscribed to Helmio alerts.';

                                return;
                            }

                            enableButton.textContent =
                                'Enable Helmio Alerts';

                            enableButton.disabled =
                                false;

                            disableButton.classList.add(
                                'hidden'
                            );

                            if (
                                'Notification' in window
                                && Notification.permission
                                    === 'denied'
                            ) {
                                status.textContent =
                                    'Notifications are blocked for Helmio. Enable them in your browser or device settings.';

                                return;
                            }

                            status.textContent =
                                'Alerts are not enabled on this device.';
                        } catch (error) {
                            console.error(
                                'Unable to read Helmio push status:',
                                error
                            );

                            status.textContent =
                                'Unable to check alert status right now.';
                        }
                    };

                await refreshState();

                enableButton.addEventListener(
                    'click',
                    async () => {
                        enableButton.disabled =
                            true;

                        disableButton.disabled =
                            true;

                        status.textContent =
                            'Enabling Helmio alerts...';

                        try {
                            await window
                                .HelmioPush
                                .subscribe();

                            status.textContent =
                                'This device will now receive Helmio alerts.';

                            await refreshState();
                        } catch (error) {
                            console.error(
                                'Unable to enable Helmio alerts:',
                                error
                            );

                            enableButton.disabled =
                                false;

                            disableButton.disabled =
                                false;

                            status.textContent =
                                error?.message
                                ?? 'Unable to enable alerts.';
                        }
                    },
                );

                disableButton.addEventListener(
                    'click',
                    async () => {
                        enableButton.disabled =
                            true;

                        disableButton.disabled =
                            true;

                        status.textContent =
                            'Disabling Helmio alerts...';

                        try {
                            await window
                                .HelmioPush
                                .unsubscribe();

                            status.textContent =
                                'Alerts are disabled on this device.';

                            await refreshState();
                        } catch (error) {
                            console.error(
                                'Unable to disable Helmio alerts:',
                                error
                            );

                            enableButton.disabled =
                                false;

                            disableButton.disabled =
                                false;

                            status.textContent =
                                error?.message
                                ?? 'Unable to disable alerts.';
                        }
                    },
                );
            },
        );
    </script>

</x-app-layout>