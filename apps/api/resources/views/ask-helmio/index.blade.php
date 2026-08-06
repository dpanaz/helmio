<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-violet-600">
                    Grounded portfolio assistant
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Ask Helmio
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Ask questions about your portfolio, audit findings,
                    timeline events and monthly reviews.
                </p>
            </div>

            <a
                href="{{ route('ask-helmio.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-500"
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

                New conversation
            </a>
        </div>
    </x-slot>

    @php
        $suggestedQuestions = [
            'What changed this month?',
            'What should I review first?',
            'Why did my Advisor Audit score change?',
            'Explain my portfolio concentration.',
            'How much am I paying in annual costs?',
            'Explain my latest monthly review.',
        ];

        $citationUrl = function (array $citation): ?string {
            $routeName = $citation['route_name'] ?? null;

            if (
                ! $routeName
                || ! Route::has($routeName)
            ) {
                return null;
            }

            $parameter =
                $citation['route_parameter']
                ?? $citation['id']
                ?? null;

            $parameterizedRoutes = [
                'monthly-reviews.show',
                'advisor-audit.history.show',
                'ai-insights.show',
            ];

            if (
                in_array(
                    $routeName,
                    $parameterizedRoutes,
                    true,
                )
                && $parameter !== null
            ) {
                return route(
                    $routeName,
                    $parameter,
                );
            }

            return route($routeName);
        };
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <div
                class="grid min-h-[calc(100vh-12rem)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:grid-cols-[19rem_minmax(0,1fr)]"
            >
                <aside class="border-b border-slate-200 bg-slate-50 lg:border-b-0 lg:border-r">
                    <div class="border-b border-slate-200 p-4">
                        <a
                            href="{{ route('ask-helmio.create') }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
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
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>

                            New conversation
                        </a>
                    </div>

                    <div class="max-h-72 overflow-y-auto p-3 lg:max-h-[calc(100vh-18rem)]">
                        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Conversations
                        </p>

                        <div class="space-y-1">
                            @forelse ($conversations as $item)
                                <a
                                    href="{{ route(
                                        'ask-helmio.show',
                                        $item
                                    ) }}"
                                    @class([
                                        'block rounded-xl px-3 py-3 transition',
                                        'bg-white shadow-sm ring-1 ring-slate-200' =>
                                            $conversation?->id === $item->id,
                                        'hover:bg-white' =>
                                            $conversation?->id !== $item->id,
                                    ])
                                >
                                    <p class="truncate text-sm font-medium text-slate-900">
                                        {{ $item->title
                                            ?: 'Portfolio conversation' }}
                                    </p>

                                    <div class="mt-1 flex items-center justify-between gap-3">
                                        <span class="text-xs text-slate-400">
                                            {{ $item->last_message_at
                                                ?->diffForHumans()
                                                ?? 'No messages' }}
                                        </span>

                                        <span class="text-xs text-slate-400">
                                            {{ $item->messages_count }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 p-4 text-center">
                                    <p class="text-sm font-medium text-slate-700">
                                        No conversations yet
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </aside>

                <main class="flex min-w-0 flex-col bg-white">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-7">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900">
                                {{ $conversation?->title
                                    ?: 'New conversation' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Answers are based on your stored Helmio data.
                            </p>
                        </div>

                        @if ($conversation)
                            <form
                                method="POST"
                                action="{{ route(
                                    'ask-helmio.archive',
                                    $conversation
                                ) }}"
                                onsubmit="return confirm('Archive this conversation?');"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                >
                                    Archive
                                </button>
                            </form>
                        @endif
                    </div>

                    <div
                        id="ask-helmio-messages"
                        class="flex-1 overflow-y-auto px-5 py-8 sm:px-8"
                    >
                        @if (
                            $conversation === null
                            || $conversation->messages->isEmpty()
                        )
                            <div class="mx-auto flex min-h-[30rem] max-w-4xl flex-col items-center justify-center text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-violet-100 text-violet-700">
                                    <svg
                                        class="h-9 w-9"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-6 text-3xl font-semibold tracking-tight text-slate-900">
                                    What would you like to understand?
                                </h3>

                                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                                    Ask about portfolio changes, costs, concentration,
                                    audit scores, findings or your latest monthly review.
                                </p>

                                <div class="mt-8 grid w-full gap-3 sm:grid-cols-2">
                                    @foreach ($suggestedQuestions as $suggestion)
                                        <form
                                            method="POST"
                                            action="{{ route('ask-helmio.store') }}"
                                        >
                                            @csrf

                                            @if ($conversation)
                                                <input
                                                    type="hidden"
                                                    name="conversation_id"
                                                    value="{{ $conversation->id }}"
                                                >
                                            @endif

                                            <input
                                                type="hidden"
                                                name="question"
                                                value="{{ $suggestion }}"
                                            >

                                            <button
                                                type="submit"
                                                class="flex h-full w-full items-center justify-between gap-4 rounded-2xl border border-slate-200 p-5 text-left text-sm font-medium text-slate-700 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-900"
                                            >
                                                <span>
                                                    {{ $suggestion }}
                                                </span>

                                                <svg
                                                    class="h-4 w-4 shrink-0"
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
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="mx-auto max-w-4xl space-y-8">
                                @foreach ($conversation->messages as $message)
                                    @if ($message->role === 'user')
                                        <div class="flex justify-end">
                                            <div class="max-w-3xl rounded-3xl rounded-br-lg bg-slate-950 px-5 py-4 text-sm leading-7 text-white">
                                                {{ $message->content }}
                                            </div>
                                        </div>
                                    @elseif ($message->role === 'assistant')
                                        @php
                                            $confidenceClasses = match (
                                                $message->confidence
                                            ) {
                                                'high' =>
                                                    'bg-emerald-100 text-emerald-800',

                                                'medium' =>
                                                    'bg-amber-100 text-amber-800',

                                                'low' =>
                                                    'bg-red-100 text-red-800',

                                                default =>
                                                    'bg-slate-100 text-slate-700',
                                            };
                                        @endphp

                                        <div class="flex items-start gap-4">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
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
                                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                                    />
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <p class="font-semibold text-slate-900">
                                                        Helmio
                                                    </p>

                                                    @if ($message->confidence)
                                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $confidenceClasses }}">
                                                            {{ str($message->confidence)->title() }}
                                                            confidence
                                                        </span>
                                                    @endif

                                                    @if ($message->status === 'failed')
                                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                                            Failed
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                                    {{ $message->content }}
                                                </div>

                                                @if (! empty($message->citations))
                                                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                                            Supporting Helmio records
                                                        </p>

                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach ($message->citations as $citation)
                                                                @php
                                                                    $url = $citationUrl(
                                                                        $citation
                                                                    );
                                                                @endphp

                                                                @if ($url)
                                                                    <a
                                                                        href="{{ $url }}"
                                                                        class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-blue-600 shadow-sm ring-1 ring-slate-200 transition hover:text-blue-500"
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
                                                                                d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M6 8.25v9.75h9.75"
                                                                            />
                                                                        </svg>

                                                                        {{ $citation['label']
                                                                            ?? 'Supporting record' }}
                                                                    </a>
                                                                @else
                                                                    <span class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                                                        {{ $citation['label']
                                                                            ?? 'Supporting record' }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (! empty($message->limitations))
                                                    <details class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                                        <summary class="cursor-pointer text-xs font-semibold text-amber-900">
                                                            Data limitations
                                                        </summary>

                                                        <div class="mt-3 space-y-2">
                                                            @foreach ($message->limitations as $limitation)
                                                                <p class="text-xs leading-5 text-amber-800">
                                                                    {{ $limitation }}
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                @endif

                                                <p class="mt-3 text-xs text-slate-400">
                                                    {{ $message->generated_at
                                                        ?->format('g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-200 bg-white p-4 sm:p-6">
                        <form
                            method="POST"
                            action="{{ route('ask-helmio.store') }}"
                            class="mx-auto max-w-4xl"
                        >
                            @csrf

                            @if ($conversation)
                                <input
                                    type="hidden"
                                    name="conversation_id"
                                    value="{{ $conversation->id }}"
                                >
                            @endif

                            <div class="flex items-end gap-3 rounded-2xl border border-slate-300 bg-white p-3 shadow-sm focus-within:border-violet-400 focus-within:ring-2 focus-within:ring-violet-100">
                                <textarea
                                    name="question"
                                    rows="2"
                                    maxlength="2000"
                                    required
                                    placeholder="Ask Helmio about your portfolio..."
                                    class="max-h-40 min-h-12 flex-1 resize-none border-0 bg-transparent px-2 py-2 text-sm text-slate-900 shadow-none focus:ring-0"
                                >{{ old('question') }}</textarea>

                                <button
                                    type="submit"
                                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white transition hover:bg-violet-500"
                                    aria-label="Send question"
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
                                            d="m4.5 12 15-7.5-4.5 15-3-6-7.5-1.5Zm7.5 1.5 7.5-9"
                                        />
                                    </svg>
                                </button>
                            </div>

                            @error('question')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-3 text-center text-xs leading-5 text-slate-400">
                                Helmio explains recorded portfolio data and does not
                                provide instructions to buy or sell investments.
                            </p>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById(
                'ask-helmio-messages'
            );

            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
</x-app-layout>