<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-500 sm:w-auto"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
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
        /*
         * Show newest question/answer pairs first, while keeping the
         * question immediately above its matching Helmio response.
         */
        $displayMessages = collect();

        if ($conversation) {
            $pairs = collect();
            $currentPair = null;

            foreach ($conversation->messages->sortBy('id') as $message) {
                if ($message->role === 'user') {
                    if ($currentPair !== null) {
                        $pairs->push($currentPair);
                    }

                    $currentPair = [
                        'sort_id' => $message->id,
                        'messages' => collect([$message]),
                    ];
                } elseif ($message->role === 'assistant') {
                    if ($currentPair === null) {
                        $currentPair = [
                            'sort_id' => $message->id,
                            'messages' => collect(),
                        ];
                    }

                    $currentPair['messages']->push($message);
                }
            }

            if ($currentPair !== null) {
                $pairs->push($currentPair);
            }

            $displayMessages = $pairs
                ->sortByDesc('sort_id')
                ->flatMap(
                    fn (array $pair) => $pair['messages']
                )
                ->values();
        }
    @endphp

    <div id="ask-helmio-page" class="min-h-screen overflow-x-hidden bg-slate-950">
        <div class="lg:hidden">
            <div class="border-b border-slate-800 bg-slate-950/95 px-3 py-2.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">
                            {{ $conversation?->title ?: 'New conversation' }}
                        </p>

                        <p class="mt-0.5 truncate text-[11px] text-slate-500">
                            Based on your stored Helmio data
                        </p>
                    </div>

                    @if ($conversation)
                        <form
                            method="POST"
                            action="{{ route('ask-helmio.archive', $conversation) }}"
                            onsubmit="return confirm('Archive this conversation?');"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="shrink-0 rounded-lg border border-slate-700 bg-slate-900 px-2.5 py-2 text-[11px] font-semibold text-slate-400"
                            >
                                Archive
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if ($conversations->isNotEmpty())
                <div class="border-b border-slate-800 bg-slate-950/80">
                    <div class="overflow-x-auto px-3 py-2.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <div class="flex min-w-max gap-2">
                            @foreach ($conversations as $item)
                                <a
                                    href="{{ route('ask-helmio.show', $item) }}"
                                    @class([
                                        'block w-[12.5rem] shrink-0 rounded-xl border px-3 py-2.5',
                                        'border-violet-500/30 bg-violet-500/[0.08]' =>
                                            $conversation?->id === $item->id,
                                        'border-slate-800 bg-slate-900/70' =>
                                            $conversation?->id !== $item->id,
                                    ])
                                >
                                    <p class="truncate text-xs font-semibold text-slate-200">
                                        {{ $item->title ?: 'Portfolio conversation' }}
                                    </p>

                                    <p class="mt-1 truncate text-[10px] text-slate-600">
                                        {{ $item->last_message_at?->diffForHumans() ?? 'No messages' }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($generationInProgress && $conversation)
                <div class="border-b border-violet-500/10 bg-violet-500/[0.04] px-3 py-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-violet-300/25 border-t-violet-300"></div>

                        <p class="text-xs font-semibold text-violet-200">
                            Helmio is thinking…
                        </p>
                    </div>
                </div>
            @endif

            <div
                id="ask-helmio-mobile-composer"
                class="border-b border-slate-800 bg-slate-950/95 px-3 py-3 backdrop-blur"
            >
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

                    <div class="flex items-end gap-2 rounded-xl border border-slate-700 bg-slate-900 p-2 shadow-lg focus-within:border-violet-500">
                        <textarea
                            name="question"
                            rows="1"
                            maxlength="2000"
                            required
                            placeholder="Ask Helmio..."
                            class="max-h-28 min-h-10 flex-1 resize-none border-0 bg-transparent px-2 py-1.5 text-[16px] leading-6 text-white placeholder-slate-600 shadow-none focus:ring-0"
                        >{{ old('question') }}</textarea>

                        <button
                            type="submit"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-600 text-white"
                            aria-label="Send question"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 15-7.5-4.5 15-3-6-7.5-1.5Zm7.5 1.5 7.5-9"/>
                            </svg>
                        </button>
                    </div>

                    @error('question')
                        <p class="mt-2 text-sm text-red-300">
                            {{ $message }}
                        </p>
                    @enderror
                </form>


            <main
                id="ask-helmio-mobile-messages"
                class="min-h-[calc(100dvh-13rem)] overflow-x-hidden overflow-y-auto pb-8"
            >

                    @if ($conversation === null || $conversation->messages->isEmpty())
                        <div class="flex min-h-[52vh] flex-col justify-center px-4 py-10">
                            <div class="mx-auto max-w-md text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"/>
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-xl font-semibold tracking-tight text-white">
                                    What would you like to understand?
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Ask about costs, risk, diversification, trading,
                                    tax efficiency, Advisor Audit, or recent changes.
                                </p>
                            </div>

                            <div class="mt-7 space-y-2.5">
                                @foreach ($suggestedQuestions as $suggestion)
                                    <form method="POST" action="{{ route('ask-helmio.store') }}">
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
                                            class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3.5 text-left transition active:bg-slate-900"
                                        >
                                            <span class="min-w-0 text-sm font-medium leading-5 text-slate-300">
                                                {{ $suggestion }}
                                            </span>

                                            <svg class="h-4 w-4 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="space-y-5 px-3 py-5">
                            @foreach ($displayMessages as $message)
                                @if ($message->role === 'user')
                                    <div class="flex justify-end">
                                        <div class="max-w-[90%] break-words rounded-2xl rounded-br-md bg-blue-600 px-4 py-3 text-sm leading-6 text-white [overflow-wrap:anywhere]">
                                            {{ $message->content }}
                                        </div>
                                    </div>

                                @elseif ($message->role === 'assistant')
                                    @php
                                        $confidenceClasses = match ($message->confidence) {
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

                                    <article class="min-w-0 rounded-2xl border border-slate-800 bg-slate-950/45 p-4">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-violet-500/20 bg-violet-500/10 text-violet-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"/>
                                                </svg>
                                            </div>

                                            <p class="font-semibold text-white">
                                                Helmio
                                            </p>

                                            @if ($message->confidence)
                                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $confidenceClasses }}">
                                                    {{ str($message->confidence)->title() }} confidence
                                                </span>
                                            @endif

                                            @if ($message->status === 'failed')
                                                <span class="rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[10px] font-semibold text-red-300">
                                                    Failed
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-3 max-w-full whitespace-pre-line break-words text-sm leading-6 text-slate-300 [overflow-wrap:anywhere]">
                                            {{ $message->content }}
                                        </div>

                                        @if (! empty($message->citations))
                                            <div class="mt-4 min-w-0 rounded-xl border border-slate-800 bg-slate-950/80 p-3">
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                    Supporting Helmio records
                                                </p>

                                                <div class="mt-3 space-y-2">
                                                    @foreach ($message->citations as $citation)
                                                        @php
                                                            $url = $citationUrl($citation);
                                                        @endphp

                                                        @if ($url)
                                                            <a
                                                                href="{{ $url }}"
                                                                class="block w-full min-w-0 break-words rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-xs font-semibold leading-5 text-blue-400 [overflow-wrap:anywhere]"
                                                            >
                                                                {{ $citation['label'] ?? 'Supporting record' }}
                                                            </a>
                                                        @else
                                                            <span class="block w-full min-w-0 break-words rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-xs font-semibold leading-5 text-slate-400 [overflow-wrap:anywhere]">
                                                                {{ $citation['label'] ?? 'Supporting record' }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if (! empty($message->limitations))
                                            <details class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] px-3 py-3">
                                                <summary class="cursor-pointer text-xs font-semibold text-amber-300">
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

                                        <p class="mt-3 text-[11px] text-slate-600">
                                            {{ $message->generated_at?->format('g:i A') }}
                                        </p>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    @endif

            </main>

            </div>
        </div>


        <div class="hidden py-8 lg:block">
            <div class="mx-auto max-w-[96rem] px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="grid min-h-[calc(100vh-12rem)] overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl lg:grid-cols-[17rem_minmax(0,1fr)]">
                    <aside class="border-r border-slate-800 bg-slate-950/80">
                        <div class="border-b border-slate-800 p-4">
                            <a
                                href="{{ route('ask-helmio.create') }}"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-violet-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>

                                New conversation
                            </a>
                        </div>

                        <div class="max-h-[calc(100vh-18rem)] overflow-y-auto p-3">
                            <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">
                                Conversations
                            </p>

                            <div class="space-y-1">
                                @forelse ($conversations as $item)
                                    <a
                                        href="{{ route('ask-helmio.show', $item) }}"
                                        @class([
                                            'block rounded-lg px-3 py-2.5 transition',
                                            'border border-violet-500/20 bg-violet-500/[0.08]' =>
                                                $conversation?->id === $item->id,
                                            'border border-transparent hover:bg-slate-900' =>
                                                $conversation?->id !== $item->id,
                                        ])
                                    >
                                        <p class="truncate text-sm font-medium text-slate-200">
                                            {{ $item->title ?: 'Portfolio conversation' }}
                                        </p>

                                        <div class="mt-1 flex items-center justify-between gap-3">
                                            <span class="text-xs text-slate-600">
                                                {{ $item->last_message_at?->diffForHumans() ?? 'No messages' }}
                                            </span>

                                            <span class="text-xs text-slate-600">
                                                {{ $item->messages_count }}
                                            </span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-800 p-4 text-center">
                                        <p class="text-sm text-slate-500">
                                            No conversations yet
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </aside>

                    <main class="flex min-w-0 flex-col bg-slate-900">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-800 bg-slate-900/95 px-7 py-4">
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
                                    action="{{ route('ask-helmio.archive', $conversation) }}"
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

                        @if ($generationInProgress && $conversation)
                            <div class="mx-auto mt-5 flex w-full max-w-5xl items-center gap-3 rounded-xl border border-violet-500/20 bg-violet-500/[0.06] px-4 py-3">
                                <div class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-violet-300/25 border-t-violet-300"></div>

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

                        <div class="border-b border-slate-800 bg-slate-950/95 p-5 backdrop-blur">
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

                                <div class="flex items-end gap-3 rounded-xl border border-slate-700 bg-slate-900 p-2.5 shadow-lg focus-within:border-violet-500 focus-within:ring-2 focus-within:ring-violet-500/10">
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
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 15-7.5-4.5 15-3-6-7.5-1.5Zm7.5 1.5 7.5-9"/>
                                        </svg>
                                    </button>
                                </div>

                                @error('question')
                                    <p class="mt-2 text-sm text-red-300">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <p class="mt-3 text-center text-xs leading-5 text-slate-600">
                                    Answers are grounded in your stored Helmio data and are for portfolio oversight,
                                    not trade execution.
                                </p>
                            </form>
                        </div>


                        <div
                            id="ask-helmio-desktop-messages"
                            class="flex-1 overflow-y-auto"
                        >

                    @if ($conversation === null || $conversation->messages->isEmpty())
                        <div class="flex min-h-[52vh] flex-col justify-center px-4 py-10">
                            <div class="mx-auto max-w-md text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"/>
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-xl font-semibold tracking-tight text-white">
                                    What would you like to understand?
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Ask about costs, risk, diversification, trading,
                                    tax efficiency, Advisor Audit, or recent changes.
                                </p>
                            </div>

                            <div class="mt-7 space-y-2.5">
                                @foreach ($suggestedQuestions as $suggestion)
                                    <form method="POST" action="{{ route('ask-helmio.store') }}">
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
                                            class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/70 px-4 py-3.5 text-left transition active:bg-slate-900"
                                        >
                                            <span class="min-w-0 text-sm font-medium leading-5 text-slate-300">
                                                {{ $suggestion }}
                                            </span>

                                            <svg class="h-4 w-4 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="space-y-5 mx-auto max-w-5xl space-y-7 px-6 py-8">
                            @foreach ($displayMessages as $message)
                                @if ($message->role === 'user')
                                    <div class="flex justify-end">
                                        <div class="max-w-[90%] break-words rounded-2xl rounded-br-md bg-blue-600 px-4 py-3 text-sm leading-6 text-white [overflow-wrap:anywhere]">
                                            {{ $message->content }}
                                        </div>
                                    </div>

                                @elseif ($message->role === 'assistant')
                                    @php
                                        $confidenceClasses = match ($message->confidence) {
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

                                    <article class="min-w-0 rounded-2xl border border-slate-800 bg-slate-950/45 p-4">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-violet-500/20 bg-violet-500/10 text-violet-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"/>
                                                </svg>
                                            </div>

                                            <p class="font-semibold text-white">
                                                Helmio
                                            </p>

                                            @if ($message->confidence)
                                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $confidenceClasses }}">
                                                    {{ str($message->confidence)->title() }} confidence
                                                </span>
                                            @endif

                                            @if ($message->status === 'failed')
                                                <span class="rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[10px] font-semibold text-red-300">
                                                    Failed
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-3 max-w-full whitespace-pre-line break-words text-sm leading-6 text-slate-300 [overflow-wrap:anywhere]">
                                            {{ $message->content }}
                                        </div>

                                        @if (! empty($message->citations))
                                            <div class="mt-4 min-w-0 rounded-xl border border-slate-800 bg-slate-950/80 p-3">
                                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                    Supporting Helmio records
                                                </p>

                                                <div class="mt-3 space-y-2">
                                                    @foreach ($message->citations as $citation)
                                                        @php
                                                            $url = $citationUrl($citation);
                                                        @endphp

                                                        @if ($url)
                                                            <a
                                                                href="{{ $url }}"
                                                                class="block w-full min-w-0 break-words rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-xs font-semibold leading-5 text-blue-400 [overflow-wrap:anywhere]"
                                                            >
                                                                {{ $citation['label'] ?? 'Supporting record' }}
                                                            </a>
                                                        @else
                                                            <span class="block w-full min-w-0 break-words rounded-lg border border-slate-700 bg-slate-900 px-3 py-2.5 text-xs font-semibold leading-5 text-slate-400 [overflow-wrap:anywhere]">
                                                                {{ $citation['label'] ?? 'Supporting record' }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if (! empty($message->limitations))
                                            <details class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] px-3 py-3">
                                                <summary class="cursor-pointer text-xs font-semibold text-amber-300">
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

                                        <p class="mt-3 text-[11px] text-slate-600">
                                            {{ $message->generated_at?->format('g:i A') }}
                                        </p>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    @endif

                        </div>

                    </main>
                </div>
            </div>
        </div>
    </div>

    <style>
        #ask-helmio-page,
        #ask-helmio-page * {
            box-sizing: border-box;
        }

        #ask-helmio-mobile-messages,
        #ask-helmio-desktop-messages {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        @media (max-width: 1023px) {
            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
            }

            #ask-helmio-page {
                width: 100%;
                max-width: 100vw;
            }

            /*
             * Global PWA install card: keep it above the mobile nav and
             * below the browser viewport edge. This rule is applied only
             * after the script positively identifies the install prompt.
             */
            [data-helmio-pwa-mobile-fix="true"] {
                position: fixed !important;
                left: 0.75rem !important;
                right: 0.75rem !important;
                bottom: calc(4.35rem + env(safe-area-inset-bottom)) !important;
                width: auto !important;
                max-width: none !important;
                max-height: min(12rem, 34vh) !important;
                overflow-y: auto !important;
                z-index: 90 !important;
                margin: 0 !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const generationInProgress = @json($generationInProgress);
            const questionMessageId = @json($questionMessageId);
            const conversationId = @json($conversation?->id);

            if (
                generationInProgress
                && conversationId
                && questionMessageId > 0
            ) {
                const statusUrl = @json(
                    $conversation
                        ? route('ask-helmio.status', $conversation)
                        : null
                );

                const cleanUrl = @json(
                    $conversation
                        ? route('ask-helmio.show', $conversation)
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
                            const data = await response.json();

                            if (data.finished) {
                                window.location.replace(cleanUrl);
                                return;
                            }
                        }
                    } catch (error) {
                        // Temporary request failures should not break the chat.
                    }

                    if (attempts < maxAttempts) {
                        window.setTimeout(poll, 2000);
                    }
                };

                window.setTimeout(poll, 1200);
            }

            /*
             * Scroll only the active chat viewport.
             */
            const mobileMessages =
                document.getElementById('ask-helmio-mobile-messages');

            const desktopMessages =
                document.getElementById('ask-helmio-desktop-messages');

            if (mobileMessages && window.innerWidth < 1024) {
                mobileMessages.scrollTop = mobileMessages.scrollHeight;
            }

            if (desktopMessages && window.innerWidth >= 1024) {
                desktopMessages.scrollTop = desktopMessages.scrollHeight;
            }

            /*
             * The PWA install prompt belongs to the global layout.
             * Find the smallest visible fixed/absolute ancestor containing
             * "Install Helmio" and move it above Helmio's mobile bottom nav.
             */
            const repositionInstallPrompt = () => {
                if (window.innerWidth >= 1024) {
                    return;
                }

                let prompt = null;

                const walker = document.createTreeWalker(
                    document.body,
                    NodeFilter.SHOW_TEXT
                );

                while (walker.nextNode()) {
                    const value =
                        walker.currentNode.nodeValue
                            ?.replace(/\s+/g, ' ')
                            .trim()
                        ?? '';

                    if (! value.includes('Install Helmio')) {
                        continue;
                    }

                    let element = walker.currentNode.parentElement;

                    while (element && element !== document.body) {
                        const style = window.getComputedStyle(element);

                        if (
                            style.position === 'fixed'
                            || style.position === 'absolute'
                        ) {
                            prompt = element;
                            break;
                        }

                        element = element.parentElement;
                    }

                    if (prompt) {
                        break;
                    }
                }

                document
                    .querySelectorAll('[data-helmio-pwa-mobile-fix]')
                    .forEach((element) => {
                        if (element !== prompt) {
                            element.removeAttribute(
                                'data-helmio-pwa-mobile-fix'
                            );
                        }
                    });

                if (! prompt) {
                    return;
                }

                prompt.setAttribute(
                    'data-helmio-pwa-mobile-fix',
                    'true'
                );
            };

            repositionInstallPrompt();

            const observer =
                new MutationObserver(repositionInstallPrompt);

            observer.observe(
                document.body,
                {
                    childList: true,
                    subtree: true,
                }
            );

            window.addEventListener(
                'resize',
                repositionInstallPrompt
            );
        });
    </script>
</x-app-layout>