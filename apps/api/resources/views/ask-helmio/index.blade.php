<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400">
                    Portfolio assistant
                </p>

                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Ask Helmio
                </h2>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                    Ask a plain-English question about your portfolio. Helmio answers from your stored scores,
                    findings, activity, and review history.
                </p>
            </div>

            <a
                href="{{ route('ask-helmio.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-500"
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
        $generationInProgress = request()->boolean('generating');
        $questionMessageId = max(
            0,
            (int) request()->query('question_message_id', 0),
        );

        $suggestedQuestions = [
            'What changed this month?',
            'What should I review first?',
            'Why did my Advisor Audit score change?',
            'Explain my portfolio concentration.',
            'How much am I paying in annual costs?',
            'Explain my latest monthly review.',
        ];

        $citationUrl = function (array $citation): ?string {
            $routeName =
                $citation['route_name']
                ?? null;

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
                    true
                )
                && $parameter !== null
            ) {
                return route(
                    $routeName,
                    $parameter
                );
            }

            return route($routeName);
        };
    @endphp

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div
                    class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            <div
                class="grid min-h-[calc(100vh-12rem)] overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl lg:grid-cols-[17rem_minmax(0,1fr)]"
            >
                <aside
                    class="border-b border-slate-800 bg-slate-950/80 lg:border-b-0 lg:border-r"
                >
                    <div class="border-b border-slate-800 p-4">
                        <a
                            href="{{ route('ask-helmio.create') }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-violet-500"
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

                    <div
                        class="max-h-72 overflow-y-auto p-3 lg:max-h-[calc(100vh-18rem)]"
                    >
                        <p
                            class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-600"
                        >
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
                                        'block rounded-lg px-3 py-2.5 transition',

                                        'border border-violet-500/20 bg-violet-500/[0.08]' =>
                                            $conversation?->id === $item->id,

                                        'border border-transparent hover:bg-slate-900' =>
                                            $conversation?->id !== $item->id,
                                    ])
                                >
                                    <p
                                        class="truncate text-sm font-medium text-slate-200"
                                    >
                                        {{ $item->title ?: 'Portfolio conversation' }}
                                    </p>

                                    <div
                                        class="mt-1 flex items-center justify-between gap-3"
                                    >
                                        <span class="text-xs text-slate-600">
                                            {{ $item->last_message_at
                                                ?->diffForHumans()
                                                ?? 'No messages' }}
                                        </span>

                                        <span class="text-xs text-slate-600">
                                            {{ $item->messages_count }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-slate-800 p-4 text-center"
                                >
                                    <p class="text-sm text-slate-500">
                                        No conversations yet
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </aside>

                <main
                    class="flex min-w-0 flex-col bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-slate-800 bg-slate-900/95 px-5 py-4 sm:px-7"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">
                                {{ $conversation?->title ?: 'New conversation' }}
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
                                    class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-400 transition hover:border-slate-600 hover:text-white"
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
                        @if ($generationInProgress && $conversation)
                            <div
                                id="ask-helmio-thinking"
                                class="mx-auto mb-5 flex max-w-5xl items-center gap-3 rounded-xl border border-violet-500/20 bg-violet-500/[0.06] px-4 py-3"
                            >
                                <div
                                    class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-violet-300/25 border-t-violet-300"
                                ></div>

                                <div>
                                    <p class="text-sm font-semibold text-violet-200">
                                        Helmio is thinking…
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Your question was saved. You can leave this page while the answer is generated.
                                    </p>
                                </div>
                            </div>
                        @endif
                        @if (
                            $conversation === null
                            || $conversation->messages->isEmpty()
                        )
                            <div class="mx-auto flex min-h-[32rem] max-w-4xl flex-col justify-center">
                                <div class="mx-auto max-w-2xl text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
                                    >
                                        <svg
                                            class="h-7 w-7"
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

                                    <h3 class="mt-5 text-2xl font-semibold tracking-tight text-white">
                                        What would you like to understand?
                                    </h3>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Ask about costs, risk, diversification, trading, tax efficiency,
                                        your Advisor Audit, or recent portfolio changes.
                                    </p>
                                </div>

                                <div class="mt-8">
                                    <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                        Try asking
                                    </p>

                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
                                                    class="group flex h-full min-h-[6.5rem] w-full flex-col justify-between rounded-xl border border-slate-800 bg-slate-950/70 p-4 text-left transition hover:border-violet-500/35 hover:bg-violet-500/[0.04]"
                                                >
                                                    <span class="text-sm font-medium leading-5 text-slate-300 group-hover:text-white">
                                                        {{ $suggestion }}
                                                    </span>

                                                    <span class="mt-4 inline-flex items-center gap-1 text-[11px] font-semibold text-violet-400">
                                                        Ask Helmio
                                                        <svg
                                                            class="h-3.5 w-3.5 transition group-hover:translate-x-0.5"
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
                                                    </span>
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-6 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] px-4 py-3">
                                    <p class="text-xs leading-5 text-slate-500">
                                        Helmio explains the portfolio data already stored in your account.
                                        It does not place trades or change your investments.
                                    </p>
                                </div>
                            </div>
                            </div>
                        @else
                            <div class="mx-auto max-w-5xl space-y-7">
                                @foreach ($conversation->messages as $message)

                                    @if ($message->role === 'user')
                                        <div class="flex justify-end">
                                            <div
                                                class="max-w-2xl rounded-2xl rounded-br-md bg-blue-600 px-4 py-3 text-sm leading-6 text-white shadow-sm"
                                            >
                                                {{ $message->content }}
                                            </div>
                                        </div>

                                    @elseif ($message->role === 'assistant')
                                        @php
                                            $confidenceClasses = match (
                                                $message->confidence
                                            ) {
                                                'high' =>
                                                    'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                                'medium' =>
                                                    'border-amber-500/20 bg-amber-500/10 text-amber-300',

                                                'low' =>
                                                    'border-red-500/20 bg-red-500/10 text-red-300',

                                                default =>
                                                    'border-slate-700 bg-slate-800 text-slate-400',
                                            };
                                        @endphp

                                        <div class="flex items-start gap-3">
                                            <div
                                                class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
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
                                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                                    />
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1 rounded-2xl border border-slate-800 bg-slate-950/45 p-5">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <p class="font-semibold text-white">
                                                        Helmio
                                                    </p>

                                                    @if ($message->confidence)
                                                        <span
                                                            class="rounded-full border px-3 py-1 text-xs font-semibold {{ $confidenceClasses }}"
                                                        >
                                                            {{ str($message->confidence)->title() }}
                                                            confidence
                                                        </span>
                                                    @endif

                                                    @if ($message->status === 'failed')
                                                        <span
                                                            class="rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-300"
                                                        >
                                                            Failed
                                                        </span>
                                                    @endif
                                                </div>

                                                <div
                                                    class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-300"
                                                >
                                                    {{ $message->content }}
                                                </div>

                                                @if (! empty($message->citations))
                                                    <div
                                                        class="mt-5 rounded-xl border border-slate-800 bg-slate-950/80 p-4"
                                                    >
                                                        <p
                                                            class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-600"
                                                        >
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
                                                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-xs font-semibold text-blue-400 transition hover:border-blue-500 hover:text-blue-300"
                                                                    >
                                                                        {{ $citation['label']
                                                                            ?? 'Supporting record' }}
                                                                    </a>
                                                                @else
                                                                    <span
                                                                        class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-400"
                                                                    >
                                                                        {{ $citation['label']
                                                                            ?? 'Supporting record' }}
                                                                    </span>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (! empty($message->limitations))
                                                    <details
                                                        class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] px-4 py-3"
                                                    >
                                                        <summary
                                                            class="cursor-pointer text-xs font-semibold text-amber-300"
                                                        >
                                                            Data limitations
                                                        </summary>

                                                        <div class="mt-3 space-y-2">
                                                            @foreach ($message->limitations as $limitation)
                                                                <p class="text-xs leading-5 text-slate-400">
                                                                    {{ $limitation }}
                                                                </p>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                @endif

                                                <p class="mt-3 text-xs text-slate-600">
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

                    <div
                        class="sticky bottom-0 border-t border-slate-800 bg-slate-950/95 p-4 backdrop-blur sm:p-5"
                    >
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

                            <div
                                class="flex items-end gap-3 rounded-xl border border-slate-700 bg-slate-900 p-2.5 shadow-lg focus-within:border-violet-500 focus-within:ring-2 focus-within:ring-violet-500/10"
                            >
                                <textarea
                                    name="question"
                                    rows="1"
                                    maxlength="2000"
                                    required
                                    placeholder="Ask Helmio about your portfolio..."
                                    class="max-h-40 min-h-11 flex-1 resize-none border-0 bg-transparent px-2 py-2 text-sm leading-6 text-white placeholder-slate-600 shadow-none focus:ring-0"
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
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p
                                class="mt-3 text-center text-xs leading-5 text-slate-600"
                            >
                                Answers are grounded in your stored Helmio data and are for portfolio oversight,
                                not trade execution.
                            </p>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            () => {
                const generationInProgress =
                    @json($generationInProgress);

                const questionMessageId =
                    @json($questionMessageId);

                const conversationId =
                    @json($conversation?->id);

                if (
                    generationInProgress
                    && conversationId
                    && questionMessageId > 0
                ) {
                    const statusUrl =
                        @json(
                            $conversation
                                ? route(
                                    'ask-helmio.status',
                                    $conversation,
                                )
                                : null
                        );

                    const cleanUrl =
                        @json(
                            $conversation
                                ? route(
                                    'ask-helmio.show',
                                    $conversation,
                                )
                                : route('ask-helmio.index')
                        );

                    let attempts = 0;
                    const maxAttempts = 120;

                    const poll = async () => {
                        attempts += 1;

                        try {
                            const response = await fetch(
                                `${statusUrl}?question_message_id=${questionMessageId}`,
                                {
                                    headers: {
                                        Accept: 'application/json',
                                    },
                                }
                            );

                            if (response.ok) {
                                const data =
                                    await response.json();

                                if (data.finished) {
                                    window.location.replace(
                                        cleanUrl
                                    );
                                    return;
                                }
                            }
                        } catch (error) {
                            // Keep polling. A temporary request failure
                            // should not break the conversation page.
                        }

                        if (attempts < maxAttempts) {
                            window.setTimeout(
                                poll,
                                2000,
                            );
                        }
                    };

                    window.setTimeout(
                        poll,
                        1200,
                    );
                }

                const container =
                    document.getElementById(
                        'ask-helmio-messages'
                    );

                if (container) {
                    container.scrollTop =
                        container.scrollHeight;
                }
            }
        );
    </script>
</x-app-layout>