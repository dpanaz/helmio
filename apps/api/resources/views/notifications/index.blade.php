<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Portfolio activity
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Notifications
                </h2>
            </div>

            @if ($unreadCount > 0)
                <form
                    method="POST"
                    action="{{ route('notifications.read-all') }}"
                >
                    @csrf
                    @method('PATCH')

                    <button
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-slate-900">
                                Activity
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $unreadCount }}
                                unread notification{{ $unreadCount === 1 ? '' : 's' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($notifications as $notification)
                        @php
                            $data = $notification->data;

                            $severityClasses = match (
                                $data['severity'] ?? 'information'
                            ) {
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
                        @endphp

                        <article
                            @class([
                                'p-6',
                                'bg-blue-50/40' =>
                                    $notification->unread(),
                            ])
                        >
                            <div class="flex flex-wrap items-start justify-between gap-6">
                                <div class="flex max-w-3xl gap-4">
                                    <div class="pt-2">
                                        <span
                                            @class([
                                                'block h-3 w-3 rounded-full',
                                                'bg-blue-600' =>
                                                    $notification->unread(),
                                                'bg-slate-300' =>
                                                    $notification->read(),
                                            ])
                                        ></span>
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClasses }}">
                                                {{ str(
                                                    $data['severity']
                                                    ?? 'information'
                                                )->title() }}
                                            </span>

                                            <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                {{ str(
                                                    $data['category']
                                                    ?? 'audit'
                                                )
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            @if ($notification->unread())
                                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                                    New
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="mt-3 font-semibold text-slate-900">
                                            {{ $data['title']
                                                ?? 'Portfolio update' }}
                                        </h4>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ $data['message']
                                                ?? 'A portfolio change was detected.' }}
                                        </p>

                                        <p class="mt-3 text-xs text-slate-400">
                                            {{ $notification
                                                ->created_at
                                                ->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3">
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
                                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                                        >
                                            {{ $data['action_label']
                                                ?? 'Review' }}
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
                                            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                                        >
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center">
                            <p class="font-semibold text-slate-900">
                                No notifications yet
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                Audit changes and portfolio alerts will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>