<x-app-layout>
    @php
        $whatIfInitialSymbol =
            $portfolio->holdings->first()?->symbol;

        $whatIfTotalValue =
            $portfolio->totalValue();

        $whatIfCash =
            $portfolio->cash;

        $whatIfOriginalHoldings =
            $portfolio->holdings
                ->sortByDesc('marketValue')
                ->map(function ($holding) use ($whatIfTotalValue) {
                    return [
                        'symbol' => $holding->symbol,
                        'name' => $holding->name,
                        'current_value' => $holding->marketValue,
                        'simulated_value' => $holding->marketValue,
                        'value_change' => 0,
                        'current_weight' =>
                            $holding->weight($whatIfTotalValue),
                        'simulated_weight' =>
                            $holding->weight($whatIfTotalValue),
                    ];
                })
                ->values();

    @endphp

    <div
        x-data="whatIfSimulator()"
        x-init="init()"
        class="min-h-screen bg-slate-950 bg-[radial-gradient(circle_at_top_right,rgba(37,99,235,0.10),transparent_28%),radial-gradient(circle_at_top_left,rgba(15,23,42,0.7),transparent_24%)]"
    >
        <div
            class="mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8"
        >
            {{-- Header --}}
            <div
                class="mb-6 flex flex-col gap-5 border-b border-slate-800/80 pb-5 xl:flex-row xl:items-end xl:justify-between"
            >
                <div class="min-w-0">
                    <div
                        class="mb-2 flex flex-wrap items-center gap-2"
                    >
                        <span
                            class="inline-flex items-center rounded-full border border-blue-500/20 bg-blue-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-blue-300"
                        >
                            Portfolio Simulator
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-xs text-slate-500"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            ></span>

                            Hypothetical only
                        </span>
                    </div>

                    <h1
                        class="text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                    >
                        What If?
                    </h1>

                    <p
                        class="mt-1 max-w-3xl text-sm leading-6 text-slate-400"
                    >
                        Model portfolio changes before making them.
                        Your actual holdings remain unchanged.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        x-on:click="newScenario"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:bg-slate-800 hover:text-white"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.9"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5v14m7-7H5"
                            />
                        </svg>

                        New Scenario
                    </button>

                    <button
                        type="button"
                        x-show="changes.length > 0"
                        x-cloak
                        x-on:click="openSaveModal"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.9"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 4.75h11.25L19.25 7.75v11.5H5V4.75Zm3 0v5h7v-5M8 19.25v-5.5h8v5.5"
                            />
                        </svg>

                        <span x-text="activeScenarioId ? 'Update Scenario' : 'Save Scenario'"></span>
                    </button>

                    <button
                        type="button"
                        x-show="changes.length > 0"
                        x-cloak
                        x-on:click="reset"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:bg-slate-800 hover:text-white"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.9"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 12a9 9 0 1 0 3-6.708M3 4.5v5.25h5.25"
                            />
                        </svg>

                        Reset
                    </button>
                </div>
            </div>

            {{-- Portfolio overview --}}
            <div
                class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3"
            >
                {{-- Current --}}
                <div
                    class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-sm shadow-black/10"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500"
                            >
                                Current Portfolio
                            </p>

                            <p
                                class="mt-2 text-2xl font-semibold tracking-tight text-white"
                            >
                                ${{ number_format($whatIfTotalValue, 0) }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-slate-400"
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
                                    d="M3.75 6.75h16.5v10.5H3.75V6.75Zm3-3h10.5M7.5 10.5h4.5"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- What if --}}
                <div
                    class="relative overflow-hidden rounded-2xl border border-blue-500/25 bg-gradient-to-br from-blue-500/10 via-slate-900/90 to-slate-900 p-5 shadow-sm shadow-blue-950/20"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-400"
                            >
                                What If Portfolio
                            </p>

                            <p
                                class="mt-2 text-2xl font-semibold tracking-tight text-white"
                                x-text="money(simulatedTotal)"
                            ></p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-300 ring-1 ring-inset ring-blue-500/20"
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
                                    d="M12 3v18m9-9H3"
                                />
                            </svg>
                        </div>
                    </div>

                    <div
                        x-show="changes.length"
                        class="mt-3 text-xs text-blue-300"
                    >
                        <span
                            x-text="changes.length"
                        ></span>

                        hypothetical
                        <span
                            x-text="changes.length === 1 ? 'change' : 'changes'"
                        ></span>
                        applied
                    </div>
                </div>

                {{-- Cash --}}
                <div
                    class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-sm shadow-black/10"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500"
                            >
                                Available Cash
                            </p>

                            <p
                                class="mt-2 text-2xl font-semibold tracking-tight text-white"
                                x-text="money(simulatedCash)"
                            ></p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 text-slate-400"
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
                                    d="M3.75 6.75h16.5v10.5H3.75V6.75Zm4.5 5.25h.008v.008H8.25V12Zm7.5 0h.008v.008h-.008V12ZM12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div
                x-show="activeScenarioId || scenarioMessage"
                x-cloak
                class="mb-5 flex flex-col gap-2 rounded-xl border border-slate-800/80 bg-slate-900/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                        Current Scenario
                    </p>

                    <p
                        class="mt-1 truncate text-sm font-semibold text-white"
                        x-text="activeScenarioName || 'Unsaved scenario'"
                    ></p>
                </div>

                <p
                    x-show="scenarioMessage"
                    class="text-xs text-emerald-300"
                    x-text="scenarioMessage"
                ></p>
            </div>


            {{-- Simulator workspace --}}
            <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
                {{-- Left column --}}
                <div class="space-y-4 xl:sticky xl:top-5">
                    {{-- Scenario builder --}}
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/85 shadow-sm shadow-black/10"
                    >
                        <div
                            class="border-b border-slate-800 px-5 py-4"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/10 text-blue-300"
                                >
                                    <svg
                                        class="h-4.5 w-4.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 6v12m6-6H6"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h2
                                        class="text-sm font-semibold text-white"
                                    >
                                        Build a Scenario
                                    </h2>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        Add hypothetical portfolio changes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 p-4 sm:p-5">
                            <div>
                                <label
                                    for="what-if-action"
                                    class="mb-1.5 block text-xs font-semibold text-slate-400"
                                >
                                    Action
                                </label>

                                <select
                                    id="what-if-action"
                                    x-model="form.action"
                                    x-on:change="setAction($event.target.value)"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="sell">
                                        Sell position
                                    </option>

                                    <option value="buy">
                                        Buy security
                                    </option>

                                    <option value="set_value">
                                        Change position value
                                    </option>

                                    <option value="remove">
                                        Remove position
                                    </option>
                                </select>
                            </div>

                            {{-- Existing holding selector --}}
                            <div
                                x-show="form.action !== 'buy'"
                                x-cloak
                            >
                                <label
                                    for="what-if-holding"
                                    class="mb-1.5 block text-xs font-semibold text-slate-400"
                                >
                                    Holding
                                </label>

                                <select
                                    id="what-if-holding"
                                    x-model="form.symbol"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
                                >
                                    @forelse(
                                        $portfolio->holdings
                                            ->sortByDesc('marketValue')
                                        as $holding
                                    )
                                        <option
                                            value="{{ $holding->symbol }}"
                                        >
                                            {{ $holding->symbol }}
                                            ·
                                            ${{ number_format(
                                                $holding->marketValue,
                                                0
                                            ) }}
                                        </option>
                                    @empty
                                        <option value="">
                                            No holdings available
                                        </option>
                                    @endforelse
                                </select>
                            </div>

                            {{-- Buy security input --}}
                            <div
                                x-show="form.action === 'buy'"
                                x-cloak
                            >
                                <label
                                    for="what-if-buy-security"
                                    class="mb-1.5 block text-xs font-semibold text-slate-400"
                                >
                                    Security to Buy
                                </label>

                                <div class="relative">
                                    <input
                                        id="what-if-buy-security"
                                        type="text"
                                        x-model="form.symbol"
                                        x-on:input="
                                            form.symbol =
                                                $event.target.value
                                                    .toUpperCase()
                                                    .replace(
                                                        /[^A-Z0-9.\-]/g,
                                                        ''
                                                    )
                                        "
                                        placeholder="AAPL, VTI, QQQ, VFIAX..."
                                        autocomplete="off"
                                        autocapitalize="characters"
                                        spellcheck="false"
                                        maxlength="30"
                                        class="block w-full rounded-xl border-slate-700 bg-slate-950 px-3 py-2.5 pr-20 text-sm font-semibold uppercase tracking-wide text-slate-200 placeholder:normal-case placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                                    >

                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        Symbol
                                    </span>
                                </div>

                                <p class="mt-1.5 text-[11px] leading-4 text-slate-600">
                                    Enter a stock, ETF, or mutual fund symbol.
                                </p>
                            </div>

<div
                                x-show="
                                    form.action === 'sell'
                                    || form.action === 'buy'
                                    || form.action === 'set_value'
                                "
                                x-cloak
                            >
                                <label
                                    for="what-if-amount"
                                    class="mb-1.5 block text-xs font-semibold text-slate-400"
                                    x-text="
                                        form.action === 'sell'
                                            ? 'Amount to sell'
                                            : (
                                                form.action === 'buy'
                                                    ? 'Amount to invest'
                                                    : 'New position value'
                                            )
                                    "
                                ></label>

                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500"
                                    >
                                        $
                                    </span>

                                    <input
                                        id="what-if-amount"
                                        x-model.number="form.amount"
                                        type="number"
                                        min="0"
                                        step="100"
                                        placeholder="0"
                                        class="block w-full rounded-xl border-slate-700 bg-slate-950 py-2.5 pl-7 pr-3 text-sm text-slate-200 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                            </div>

                            <button
                                type="button"
                                x-on:click="addChange"
                                x-bind:disabled="loading"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    x-show="!loading"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 5v14m7-7H5"
                                    />
                                </svg>

                                <svg
                                    x-show="loading"
                                    x-cloak
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                    ></circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z"
                                    ></path>
                                </svg>

                                <span
                                    x-text="
                                        loading
                                            ? 'Recalculating...'
                                            : 'Add Change'
                                    "
                                ></span>
                            </button>

                            <div
                                x-show="error"
                                x-cloak
                                class="rounded-xl border border-red-500/20 bg-red-500/10 px-3.5 py-3 text-xs leading-5 text-red-300"
                                x-text="error"
                            ></div>
                        </div>
                    </div>

                    {{-- Scenario changes --}}
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/85 shadow-sm shadow-black/10"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-800 px-5 py-4"
                        >
                            <div>
                                <h3
                                    class="text-sm font-semibold text-white"
                                >
                                    Scenario Changes
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Changes are applied in order.
                                </p>
                            </div>

                            <span
                                class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold text-slate-400"
                                x-text="changes.length"
                            ></span>
                        </div>

                        <div class="p-4">
                            <div
                                x-show="changes.length === 0"
                                class="rounded-xl border border-dashed border-slate-800 px-4 py-6 text-center"
                            >
                                <div
                                    class="mx-auto flex h-9 w-9 items-center justify-center rounded-xl bg-slate-800 text-slate-500"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 6v12m6-6H6"
                                        />
                                    </svg>
                                </div>

                                <p
                                    class="mt-3 text-xs text-slate-500"
                                >
                                    No hypothetical changes yet.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <template
                                    x-for="(change, index) in changes"
                                    :key="index"
                                >
                                    <div
                                        class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/70 px-3 py-3"
                                    >
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-300"
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
                                                    d="M12 5v14m7-7H5"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-xs font-semibold text-slate-200"
                                                x-text="changeLabel(change)"
                                            ></p>

                                            <p
                                                x-show="change.amount"
                                                class="mt-0.5 text-[11px] text-slate-500"
                                                x-text="money(change.amount)"
                                            ></p>
                                        </div>

                                        <button
                                            type="button"
                                            x-on:click="removeChange(index)"
                                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-500/10 hover:text-red-300"
                                            aria-label="Remove change"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.9"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M6 18 18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Saved scenarios --}}
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/85 shadow-sm shadow-black/10"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-800 px-5 py-4"
                        >
                            <div>
                                <h3 class="text-sm font-semibold text-white">
                                    Saved Scenarios
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Reload a scenario against your current portfolio.
                                </p>
                            </div>

                            <span
                                class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold text-slate-400"
                                x-text="savedScenarios.length"
                            ></span>
                        </div>

                        <div class="p-4">
                            <div
                                x-show="loadingScenarios"
                                class="rounded-xl border border-dashed border-slate-800 px-4 py-5 text-center text-xs text-slate-500"
                            >
                                Loading saved scenarios...
                            </div>

                            <div
                                x-show="!loadingScenarios && savedScenarios.length === 0"
                                x-cloak
                                class="rounded-xl border border-dashed border-slate-800 px-4 py-5 text-center"
                            >
                                <p class="text-xs text-slate-500">
                                    No saved scenarios yet.
                                </p>
                            </div>

                            <div
                                x-show="savedScenarios.length > 0"
                                x-cloak
                                class="space-y-2"
                            >
                                <template
                                    x-for="scenario in savedScenarios"
                                    :key="scenario.id"
                                >
                                    <div
                                        class="rounded-xl border px-3 py-3 transition"
                                        :class="
                                            activeScenarioId === scenario.id
                                                ? 'border-blue-500/30 bg-blue-500/5'
                                                : 'border-slate-800 bg-slate-950/60'
                                        "
                                    >
                                        <div class="flex items-start gap-3">
                                            <button
                                                type="button"
                                                x-on:click="loadScenario(scenario)"
                                                class="min-w-0 flex-1 text-left"
                                            >
                                                <p
                                                    class="truncate text-xs font-semibold text-slate-200"
                                                    x-text="scenario.name"
                                                ></p>

                                                <p
                                                    class="mt-1 text-[11px] text-slate-500"
                                                    x-text="
                                                        scenario.change_count
                                                        + ' '
                                                        + (
                                                            scenario.change_count === 1
                                                                ? 'change'
                                                                : 'changes'
                                                        )
                                                    "
                                                ></p>
                                            </button>

                                            <div class="flex shrink-0 items-center gap-1">
                                                <button
                                                    type="button"
                                                    x-on:click="duplicateScenario(scenario)"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-blue-500/10 hover:text-blue-300"
                                                    title="Duplicate scenario"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M8 7.5V5h11v11h-2.5M5 8h11v11H5V8Z"
                                                        />
                                                    </svg>
                                                </button>

                                                <button
                                                    type="button"
                                                    x-on:click="deleteScenario(scenario)"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-500/10 hover:text-red-300"
                                                    title="Delete scenario"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 7h12m-9 0V5h6v2m-7 0 .75 12h6.5L16 7"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                {{-- Results workspace --}}
                <div class="min-w-0 space-y-5">
                {{-- Scenario impact --}}
                <div
                    x-show="changes.length > 0 && impact.largestPosition"
                    x-cloak
                    class="min-w-0"
                >
                    <div
                        class="rounded-2xl border border-slate-800/90 bg-slate-900/80 p-5 shadow-sm shadow-black/10"
                    >
                        <div class="mb-4">
                            <h2 class="text-sm font-semibold text-white">
                                Scenario Impact
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Immediate portfolio effects from the hypothetical changes.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                    Largest Position
                                </p>

                                <p
                                    class="mt-2 text-lg font-semibold text-white"
                                    x-text="
                                        impact.largestPosition?.simulated?.symbol
                                        ?? '—'
                                    "
                                ></p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                    x-text="
                                        impact.largestPosition?.simulated
                                            ? percent(impact.largestPosition.simulated.weight)
                                            : '—'
                                    "
                                ></p>
                            </div>

                            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                    Cash Allocation
                                </p>

                                <p
                                    class="mt-2 text-lg font-semibold text-white"
                                    x-text="
                                        impact.cash
                                            ? percent(impact.cash.simulated_weight)
                                            : '—'
                                    "
                                ></p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                    x-text="
                                        impact.cash
                                            ? (
                                                percent(impact.cash.current_weight)
                                                + ' current'
                                            )
                                            : ''
                                    "
                                ></p>
                            </div>

                            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                    Holdings
                                </p>

                                <p
                                    class="mt-2 text-lg font-semibold text-white"
                                    x-text="
                                        impact.holdingsCount?.simulated
                                        ?? '—'
                                    "
                                ></p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                    x-text="
                                        impact.holdingsCount
                                            ? (
                                                impact.holdingsCount.current
                                                + ' current'
                                            )
                                            : ''
                                    "
                                ></p>
                            </div>

                            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                    Est. Fund Costs
                                </p>

                                <p
                                    class="mt-2 text-lg font-semibold text-white"
                                    x-text="
                                        impact.fundExpenses
                                            ? money(
                                                impact.fundExpenses
                                                    .simulated_annual_estimate
                                            )
                                            : '—'
                                    "
                                ></p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                    x-text="
                                        impact.fundExpenses
                                            ? (
                                                signedMoney(
                                                    impact.fundExpenses.difference
                                                )
                                                + ' / yr'
                                            )
                                            : ''
                                    "
                                ></p>
                            </div>
                        </div>

                        <div
                            x-show="impact.summary.length > 0"
                            class="mt-4 grid gap-2 lg:grid-cols-2"
                        >
                            <template
                                x-for="(item, index) in impact.summary"
                                :key="index"
                            >
                                <div
                                    class="rounded-xl border px-4 py-3"
                                    :class="
                                        item.type === 'positive'
                                            ? 'border-emerald-500/20 bg-emerald-500/5'
                                            : (
                                                item.type === 'warning'
                                                    ? 'border-amber-500/20 bg-amber-500/5'
                                                    : 'border-slate-800 bg-slate-950/50'
                                            )
                                    "
                                >
                                    <p
                                        class="text-xs font-semibold"
                                        :class="
                                            item.type === 'positive'
                                                ? 'text-emerald-300'
                                                : (
                                                    item.type === 'warning'
                                                        ? 'text-amber-300'
                                                        : 'text-slate-300'
                                                )
                                        "
                                        x-text="item.title"
                                    ></p>

                                    <p
                                        class="mt-1 text-xs leading-5 text-slate-500"
                                        x-text="item.message"
                                    ></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Main comparison --}}
                <div
                    class="min-w-0 overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/80 shadow-sm shadow-black/10"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div
                                class="flex items-center gap-2"
                            >
                                <h2
                                    class="text-sm font-semibold text-white"
                                >
                                    Current vs What If
                                </h2>

                                <span
                                    x-show="changes.length"
                                    x-cloak
                                    class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-300"
                                >
                                    Live Scenario
                                </span>
                            </div>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Compare portfolio values and allocation weights.
                            </p>
                        </div>

                        <div
                            x-show="loading"
                            x-cloak
                            class="inline-flex items-center gap-2 text-xs font-medium text-blue-300"
                        >
                            <span
                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-400"
                            ></span>

                            Recalculating
                        </div>
                    </div>

                    <div
                        x-show="comparisonHoldings.length === 0"
                        class="px-5 py-12 text-center"
                    >
                        <p class="text-sm text-slate-400">
                            No portfolio holdings found.
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-600"
                        >
                            Connect or add an investment account to use
                            the simulator.
                        </p>
                    </div>

                    <div
                        x-show="comparisonHoldings.length > 0"
                        class="overflow-x-auto"
                    >
                        <table
                            class="min-w-full divide-y divide-slate-800"
                        >
                            <thead class="bg-slate-950/50">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        Holding
                                    </th>

                                    <th
                                        class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        Current
                                    </th>

                                    <th
                                        class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        What If
                                    </th>

                                    <th
                                        class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"
                                    >
                                        Change
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-800/80"
                            >
                                <template
                                    x-for="holding in comparisonHoldings"
                                    :key="holding.symbol"
                                >
                                    <tr
                                        class="transition hover:bg-slate-800/30"
                                    >
                                        <td
                                            class="whitespace-nowrap px-5 py-4"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-[11px] font-bold text-slate-300"
                                                    x-text="holding.symbol.substring(0, 3)"
                                                ></div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="text-sm font-semibold text-white"
                                                        x-text="holding.symbol"
                                                    ></p>

                                                    <p
                                                        class="max-w-[230px] truncate text-xs text-slate-500"
                                                        x-text="holding.name"
                                                    ></p>
                                                </div>
                                            </div>
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-right"
                                        >
                                            <p
                                                class="text-sm font-medium text-slate-300"
                                                x-text="money(holding.current_value)"
                                            ></p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-600"
                                                x-text="percent(holding.current_weight)"
                                            ></p>
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-right"
                                        >
                                            <p
                                                class="text-sm font-semibold text-white"
                                                x-text="money(holding.simulated_value)"
                                            ></p>

                                            <p
                                                class="mt-0.5 text-xs text-blue-400"
                                                x-text="percent(holding.simulated_weight)"
                                            ></p>
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-right"
                                        >
                                            <span
                                                class="inline-flex items-center rounded-lg px-2 py-1 text-xs font-semibold"
                                                :class="
                                                    holding.value_change > 0
                                                        ? 'bg-emerald-500/10 text-emerald-300'
                                                        : (
                                                            holding.value_change < 0
                                                                ? 'bg-red-500/10 text-red-300'
                                                                : 'bg-slate-800 text-slate-500'
                                                        )
                                                "
                                                x-text="
                                                    signedMoney(
                                                        holding.value_change
                                                    )
                                                "
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer notice --}}
                    <div
                        class="border-t border-slate-800 bg-slate-950/40 px-5 py-4"
                    >
                        <div
                            class="flex items-start gap-3"
                        >
                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m9-1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 16.5h.008v.008H12V16.5Z"
                                />
                            </svg>

                            <p
                                class="text-[11px] leading-5 text-slate-600"
                            >
                                What If scenarios are hypothetical and
                                do not place trades or modify your actual
                                investment accounts.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analytics comparison --}}
            <div
                x-show="changes.length > 0 && analytics.current && analytics.simulated"
                x-cloak
                class="overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/80 shadow-sm shadow-black/10"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-semibold text-white">
                                Portfolio Analytics
                            </h2>

                            <span
                                class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-300"
                            >
                                Current → What If
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            Estimated impact based on the hypothetical portfolio.
                        </p>
                    </div>

                    <div
                        x-show="loading"
                        class="inline-flex items-center gap-2 text-xs font-medium text-blue-300"
                    >
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-400"></span>
                        Updating analytics
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-px bg-slate-800 sm:grid-cols-2 xl:grid-cols-4">
                    {{-- Diversification --}}
                    <div class="bg-slate-900 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            Diversification
                        </p>

                        <div class="mt-3 flex items-center gap-3">
                            <span
                                class="text-xl font-semibold text-slate-400"
                                x-text="score(productionAnalytics.current?.categories?.diversification?.score
                                    ?? analytics.current?.diversification_score)"
                            ></span>

                            <svg
                                class="h-4 w-4 shrink-0 text-slate-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-4-4 4 4-4 4"
                                />
                            </svg>

                            <span
                                class="text-2xl font-semibold"
                                :class="scoreColor(
                                    productionAnalytics.simulated?.categories?.diversification?.score
                                    ?? analytics.simulated?.diversification_score,
                                    productionAnalytics.current?.categories?.diversification?.score
                                    ?? analytics.current?.diversification_score
                                )"
                                x-text="score(productionAnalytics.simulated?.categories?.diversification?.score
                                    ?? analytics.simulated?.diversification_score)"
                            ></span>
                        </div>

                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div
                                class="h-full rounded-full bg-blue-500 transition-all duration-300"
                                :style="`width: ${scoreWidth(productionAnalytics.simulated?.categories?.diversification?.score
                                    ?? analytics.simulated?.diversification_score)}%`"
                            ></div>
                        </div>

                        <p
                            class="mt-2 text-xs"
                            :class="deltaColor(
                                productionAnalytics.simulated?.categories?.diversification?.score
                                    ?? analytics.simulated?.diversification_score,
                                productionAnalytics.current?.categories?.diversification?.score
                                    ?? analytics.current?.diversification_score
                            )"
                            x-text="scoreDelta(
                                productionAnalytics.current?.categories?.diversification?.score
                                    ?? analytics.current?.diversification_score,
                                productionAnalytics.simulated?.categories?.diversification?.score
                                    ?? analytics.simulated?.diversification_score
                            )"
                        ></p>
                    </div>

                    {{-- Cost --}}
                    <div class="bg-slate-900 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            Cost
                        </p>

                        <div class="mt-3 flex items-center gap-3">
                            <span
                                class="text-xl font-semibold text-slate-400"
                                x-text="score(productionAnalytics.current?.categories?.cost?.score
                                    ?? analytics.current?.cost_score)"
                            ></span>

                            <svg
                                class="h-4 w-4 shrink-0 text-slate-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-4-4 4 4-4 4"
                                />
                            </svg>

                            <span
                                class="text-2xl font-semibold"
                                :class="scoreColor(
                                    productionAnalytics.simulated?.categories?.cost?.score
                                    ?? analytics.simulated?.cost_score,
                                    productionAnalytics.current?.categories?.cost?.score
                                    ?? analytics.current?.cost_score
                                )"
                                x-text="score(productionAnalytics.simulated?.categories?.cost?.score
                                    ?? analytics.simulated?.cost_score)"
                            ></span>
                        </div>

                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div
                                class="h-full rounded-full bg-blue-500 transition-all duration-300"
                                :style="`width: ${scoreWidth(productionAnalytics.simulated?.categories?.cost?.score
                                    ?? analytics.simulated?.cost_score)}%`"
                            ></div>
                        </div>

                        <p
                            class="mt-2 text-xs"
                            :class="deltaColor(
                                productionAnalytics.simulated?.categories?.cost?.score
                                    ?? analytics.simulated?.cost_score,
                                productionAnalytics.current?.categories?.cost?.score
                                    ?? analytics.current?.cost_score
                            )"
                            x-text="scoreDelta(
                                productionAnalytics.current?.categories?.cost?.score
                                    ?? analytics.current?.cost_score,
                                productionAnalytics.simulated?.categories?.cost?.score
                                    ?? analytics.simulated?.cost_score
                            )"
                        ></p>
                    </div>

                    {{-- Cash Drag --}}
                    <div class="bg-slate-900 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            Cash Drag
                        </p>

                        <div class="mt-3 flex items-center gap-3">
                            <span
                                class="text-xl font-semibold text-slate-400"
                                x-text="score(analytics.current?.cash_drag_score)"
                            ></span>

                            <svg
                                class="h-4 w-4 shrink-0 text-slate-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-4-4 4 4-4 4"
                                />
                            </svg>

                            <span
                                class="text-2xl font-semibold"
                                :class="scoreColor(
                                    analytics.simulated?.cash_drag_score,
                                    analytics.current?.cash_drag_score
                                )"
                                x-text="score(analytics.simulated?.cash_drag_score)"
                            ></span>
                        </div>

                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div
                                class="h-full rounded-full bg-blue-500 transition-all duration-300"
                                :style="`width: ${scoreWidth(analytics.simulated?.cash_drag_score)}%`"
                            ></div>
                        </div>

                        <p
                            class="mt-2 text-xs"
                            :class="deltaColor(
                                analytics.simulated?.cash_drag_score,
                                analytics.current?.cash_drag_score
                            )"
                            x-text="scoreDelta(
                                analytics.current?.cash_drag_score,
                                analytics.simulated?.cash_drag_score
                            )"
                        ></p>
                    </div>

                    {{-- Concentration --}}
                    <div class="bg-slate-900 p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            Concentration
                        </p>

                        <div class="mt-3 flex items-center gap-2 text-sm">
                            <span
                                class="text-slate-400"
                                x-text="analytics.current?.concentration_label ?? '—'"
                            ></span>

                            <svg
                                class="h-4 w-4 shrink-0 text-slate-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-4-4 4 4-4 4"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-2 text-lg font-semibold text-white"
                            x-text="analytics.simulated?.concentration_label ?? '—'"
                        ></p>

                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                HHI
                            </span>

                            <span
                                class="font-medium text-slate-300"
                                x-text="number3(analytics.simulated?.concentration_hhi)"
                            ></span>
                        </div>

                        <p
                            class="mt-2 text-xs"
                            :class="concentrationDeltaClass()"
                            x-text="concentrationDeltaText()"
                        ></p>
                    </div>
                </div>

                <div
                    class="grid gap-3 border-t border-slate-800 bg-slate-950/30 p-5 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                            Largest Position
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold text-slate-200"
                            x-text="
                                (analytics.simulated?.largest_position?.symbol ?? '—')
                                + ' · '
                                + percent(analytics.simulated?.largest_position?.weight ?? 0)
                            "
                        ></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                            Top 5 Weight
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold text-slate-200"
                            x-text="percent(analytics.simulated?.top_five_weight ?? 0)"
                        ></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                            Weighted Expense Ratio
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold text-slate-200"
                            x-text="percent(analytics.simulated?.weighted_expense_ratio ?? 0)"
                        ></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                            Annual Fund Expenses
                        </p>

                        <p
                            class="mt-1 text-sm font-semibold text-slate-200"
                            x-text="money(
                                analytics.simulated?.estimated_annual_fund_expenses ?? 0
                            )"
                        ></p>
                    </div>
                </div>
            </div>

                </div>
            </div>
        </div>
        {{-- Save scenario modal --}}
        <div
            x-show="saveModalOpen"
            x-cloak
            x-on:keydown.escape.window="saveModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4"
        >
            <div
                x-on:click.outside="saveModalOpen = false"
                class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl"
            >
                <div class="border-b border-slate-800 px-5 py-4">
                    <h3
                        class="text-base font-semibold text-white"
                        x-text="activeScenarioId ? 'Update Scenario' : 'Save Scenario'"
                    ></h3>

                    <p class="mt-1 text-xs text-slate-500">
                        Save these hypothetical changes so you can revisit them later.
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <label
                            for="scenario-name"
                            class="mb-1.5 block text-xs font-semibold text-slate-400"
                        >
                            Scenario name
                        </label>

                        <input
                            id="scenario-name"
                            type="text"
                            maxlength="120"
                            x-model="scenarioForm.name"
                            placeholder="e.g. Reduce tech concentration"
                            class="block w-full rounded-xl border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-slate-200 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label
                            for="scenario-description"
                            class="mb-1.5 block text-xs font-semibold text-slate-400"
                        >
                            Description
                            <span class="font-normal text-slate-600">
                                optional
                            </span>
                        </label>

                        <textarea
                            id="scenario-description"
                            rows="3"
                            maxlength="1000"
                            x-model="scenarioForm.description"
                            placeholder="What are you testing?"
                            class="block w-full rounded-xl border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-slate-200 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div
                        x-show="saveError"
                        x-cloak
                        class="rounded-xl border border-red-500/20 bg-red-500/10 px-3.5 py-3 text-xs text-red-300"
                        x-text="saveError"
                    ></div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-800 px-5 py-4">
                    <button
                        type="button"
                        x-on:click="saveModalOpen = false"
                        class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        x-on:click="saveScenario"
                        x-bind:disabled="savingScenario"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span
                            x-text="
                                savingScenario
                                    ? 'Saving...'
                                    : (
                                        activeScenarioId
                                            ? 'Update Scenario'
                                            : 'Save Scenario'
                                    )
                            "
                        ></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function whatIfSimulator() {
            const originalHoldings =
                @json($whatIfOriginalHoldings);

            const initialTotal =
                @json($whatIfTotalValue);

            const initialCash =
                @json($whatIfCash);

            const initialSymbol =
                @json($whatIfInitialSymbol);

            const scenarioIndexUrl =
                @json(route('what-if.scenarios.index'));

            const scenarioStoreUrl =
                @json(route('what-if.scenarios.store'));

            return {
                changes: [],

                comparisonHoldings:
                    originalHoldings,

                simulatedTotal:
                    initialTotal,

                simulatedCash:
                    initialCash,

                impact: {
                    largestPosition: null,
                    cash: null,
                    holdingsCount: null,
                    fundExpenses: null,
                    concentration: null,
                    summary: [],
                },

                analytics: {
                    current: null,
                    simulated: null,
                },

                productionAnalytics: {
                    current: null,
                    simulated: null,
                },

                savedScenarios: [],

                loadingScenarios: false,

                activeScenarioId: null,

                activeScenarioName: null,

                saveModalOpen: false,

                savingScenario: false,

                saveError: null,

                scenarioMessage: null,

                scenarioForm: {
                    name: '',
                    description: '',
                },

                loading: false,

                error: null,

                form: {
                    action: 'sell',
                    symbol: initialSymbol,
                    amount: null,
                },

                async init() {
                    await this.loadSavedScenarios();
                },

                csrfToken() {
                    return document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content;
                },

                scenarioUrl(id) {
                    return scenarioIndexUrl + '/' + id;
                },

                openSaveModal() {
                    this.saveError = null;

                    if (
                        !this.scenarioForm.name
                        && this.activeScenarioName
                    ) {
                        this.scenarioForm.name =
                            this.activeScenarioName;
                    }

                    this.saveModalOpen = true;
                },

                newScenario() {
                    this.activeScenarioId = null;
                    this.activeScenarioName = null;

                    this.scenarioForm = {
                        name: '',
                        description: '',
                    };

                    this.scenarioMessage = null;

                    this.reset();
                },

                async loadSavedScenarios() {
                    this.loadingScenarios = true;

                    try {
                        const response =
                            await fetch(
                                scenarioIndexUrl,
                                {
                                    headers: {
                                        'Accept':
                                            'application/json',
                                    },
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            || !data.success
                        ) {
                            throw new Error(
                                data.message
                                || 'Unable to load saved scenarios.'
                            );
                        }

                        this.savedScenarios =
                            data.scenarios ?? [];
                    } catch (error) {
                        console.error(error);

                        this.error =
                            error?.message
                            || 'Unable to load saved scenarios.';
                    } finally {
                        this.loadingScenarios = false;
                    }
                },

                async loadScenario(scenario) {
                    this.activeScenarioId =
                        scenario.id;

                    this.activeScenarioName =
                        scenario.name;

                    this.scenarioForm = {
                        name:
                            scenario.name ?? '',

                        description:
                            scenario.description ?? '',
                    };

                    this.changes =
                        (scenario.changes ?? [])
                            .map(change => ({
                                action:
                                    change.action,

                                symbol:
                                    change.symbol ?? null,

                                amount:
                                    change.amount ?? null,

                                percentage:
                                    change.percentage ?? null,

                                advisory_fee_rate:
                                    change.advisory_fee_rate ?? null,
                            }));

                    this.scenarioMessage =
                        'Scenario loaded and recalculated.';

                    if (this.changes.length > 0) {
                        await this.runSimulation();
                    } else {
                        this.resetResults();
                    }
                },

                async saveScenario() {
                    this.saveError = null;

                    const name =
                        String(
                            this.scenarioForm.name
                            || ''
                        ).trim();

                    if (!name) {
                        this.saveError =
                            'Enter a scenario name.';

                        return;
                    }

                    if (this.changes.length === 0) {
                        this.saveError =
                            'Add at least one hypothetical change before saving.';

                        return;
                    }

                    const token =
                        this.csrfToken();

                    if (!token) {
                        this.saveError =
                            'CSRF token is missing.';

                        return;
                    }

                    this.savingScenario = true;

                    try {
                        const isUpdate =
                            Boolean(
                                this.activeScenarioId
                            );

                        const response =
                            await fetch(
                                isUpdate
                                    ? this.scenarioUrl(
                                        this.activeScenarioId
                                    )
                                    : scenarioStoreUrl,
                                {
                                    method:
                                        isUpdate
                                            ? 'PUT'
                                            : 'POST',

                                    headers: {
                                        'Content-Type':
                                            'application/json',

                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            token,
                                    },

                                    body:
                                        JSON.stringify({
                                            name:
                                                name,

                                            description:
                                                this.scenarioForm.description
                                                || null,

                                            changes:
                                                this.changes,
                                        }),
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            || !data.success
                        ) {
                            throw new Error(
                                data.message
                                || 'Unable to save scenario.'
                            );
                        }

                        this.activeScenarioId =
                            data.scenario.id;

                        this.activeScenarioName =
                            data.scenario.name;

                        this.scenarioForm = {
                            name:
                                data.scenario.name,

                            description:
                                data.scenario.description
                                ?? '',
                        };

                        this.saveModalOpen = false;

                        this.scenarioMessage =
                            isUpdate
                                ? 'Scenario updated.'
                                : 'Scenario saved.';

                        await this.loadSavedScenarios();
                    } catch (error) {
                        console.error(error);

                        this.saveError =
                            error?.message
                            || 'Unable to save scenario.';
                    } finally {
                        this.savingScenario = false;
                    }
                },

                async duplicateScenario(scenario) {
                    const token =
                        this.csrfToken();

                    if (!token) {
                        this.error =
                            'CSRF token is missing.';

                        return;
                    }

                    try {
                        const response =
                            await fetch(
                                this.scenarioUrl(
                                    scenario.id
                                ) + '/duplicate',
                                {
                                    method: 'POST',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            token,
                                    },
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            || !data.success
                        ) {
                            throw new Error(
                                data.message
                                || 'Unable to duplicate scenario.'
                            );
                        }

                        this.scenarioMessage =
                            'Scenario duplicated.';

                        await this.loadSavedScenarios();
                    } catch (error) {
                        console.error(error);

                        this.error =
                            error?.message
                            || 'Unable to duplicate scenario.';
                    }
                },

                async deleteScenario(scenario) {
                    if (
                        !window.confirm(
                            `Delete "${scenario.name}"?`
                        )
                    ) {
                        return;
                    }

                    const token =
                        this.csrfToken();

                    if (!token) {
                        this.error =
                            'CSRF token is missing.';

                        return;
                    }

                    try {
                        const response =
                            await fetch(
                                this.scenarioUrl(
                                    scenario.id
                                ),
                                {
                                    method: 'DELETE',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            token,
                                    },
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            || !data.success
                        ) {
                            throw new Error(
                                data.message
                                || 'Unable to delete scenario.'
                            );
                        }

                        if (
                            this.activeScenarioId
                            === scenario.id
                        ) {
                            this.activeScenarioId = null;
                            this.activeScenarioName = null;

                            this.scenarioForm = {
                                name: '',
                                description: '',
                            };
                        }

                        this.scenarioMessage =
                            'Scenario deleted.';

                        await this.loadSavedScenarios();
                    } catch (error) {
                        console.error(error);

                        this.error =
                            error?.message
                            || 'Unable to delete scenario.';
                    }
                },

                money(value) {
                    return new Intl.NumberFormat(
                        'en-US',
                        {
                            style: 'currency',
                            currency: 'USD',
                            maximumFractionDigits: 0,
                        }
                    ).format(
                        Number(value || 0)
                    );
                },

                signedMoney(value) {
                    value = Number(
                        value || 0
                    );

                    if (value === 0) {
                        return '—';
                    }

                    return (
                        value > 0
                            ? '+'
                            : '-'
                    ) + this.money(
                        Math.abs(value)
                    );
                },

                percent(value) {
                    return new Intl.NumberFormat(
                        'en-US',
                        {
                            style: 'percent',
                            maximumFractionDigits: 1,
                        }
                    ).format(
                        Number(value || 0)
                    );
                },

                score(value) {
                    const number = Number(value);

                    if (!Number.isFinite(number)) {
                        return '—';
                    }

                    return Math.round(number);
                },

                scoreWidth(value) {
                    const number = Number(value);

                    if (!Number.isFinite(number)) {
                        return 0;
                    }

                    return Math.max(
                        0,
                        Math.min(100, number)
                    );
                },

                scoreDelta(current, simulated) {
                    const before = Number(current);
                    const after = Number(simulated);

                    if (
                        !Number.isFinite(before)
                        || !Number.isFinite(after)
                    ) {
                        return '—';
                    }

                    const difference =
                        Math.round(after - before);

                    if (difference === 0) {
                        return 'No change';
                    }

                    return (
                        difference > 0 ? '+' : ''
                    ) + difference + ' points';
                },

                scoreColor(simulated, current) {
                    const after = Number(simulated);
                    const before = Number(current);

                    if (
                        !Number.isFinite(after)
                        || !Number.isFinite(before)
                    ) {
                        return 'text-white';
                    }

                    if (after > before) {
                        return 'text-emerald-300';
                    }

                    if (after < before) {
                        return 'text-amber-300';
                    }

                    return 'text-white';
                },

                deltaColor(simulated, current) {
                    const after = Number(simulated);
                    const before = Number(current);

                    if (
                        !Number.isFinite(after)
                        || !Number.isFinite(before)
                        || after === before
                    ) {
                        return 'text-slate-500';
                    }

                    return after > before
                        ? 'text-emerald-400'
                        : 'text-amber-400';
                },

                number3(value) {
                    const number = Number(value);

                    if (!Number.isFinite(number)) {
                        return '—';
                    }

                    return number.toFixed(3);
                },

                concentrationDeltaText() {
                    const current =
                        Number(
                            this.analytics.current
                                ?.concentration_hhi
                        );

                    const simulated =
                        Number(
                            this.analytics.simulated
                                ?.concentration_hhi
                        );

                    if (
                        !Number.isFinite(current)
                        || !Number.isFinite(simulated)
                    ) {
                        return '—';
                    }

                    const difference =
                        simulated - current;

                    if (Math.abs(difference) < 0.0005) {
                        return 'No meaningful change';
                    }

                    return difference < 0
                        ? 'Concentration decreased'
                        : 'Concentration increased';
                },

                concentrationDeltaClass() {
                    const current =
                        Number(
                            this.analytics.current
                                ?.concentration_hhi
                        );

                    const simulated =
                        Number(
                            this.analytics.simulated
                                ?.concentration_hhi
                        );

                    if (
                        !Number.isFinite(current)
                        || !Number.isFinite(simulated)
                        || Math.abs(simulated - current) < 0.0005
                    ) {
                        return 'text-slate-500';
                    }

                    return simulated < current
                        ? 'text-emerald-400'
                        : 'text-amber-400';
                },

                changeLabel(change) {
                    const actionLabels = {
                        buy: 'Buy',
                        sell: 'Sell',
                        set_value: 'Set value',
                        remove: 'Remove',
                    };

                    return (
                        actionLabels[change.action]
                        ?? change.action
                    ) + ' ' + change.symbol;
                },

                setAction(action) {
                    this.form.action = action;

                    if (action === 'buy') {
                        this.form.symbol = '';
                    } else {
                        this.form.symbol =
                            originalHoldings.length
                                ? originalHoldings[0].symbol
                                : '';
                    }

                    this.form.amount = null;
                    this.error = null;
                },

                async addChange() {
                    this.error = null;

                    if (!this.form.symbol) {
                        this.error =
                            this.form.action === 'buy'
                                ? 'Choose a security to buy.'
                                : 'Choose a holding first.';

                        return;
                    }

                    if (
                        this.form.action !== 'remove'
                        && Number(
                            this.form.amount || 0
                        ) <= 0
                    ) {
                        this.error =
                            'Enter an amount greater than zero.';

                        return;
                    }

                    this.changes.push({
                        action:
                            this.form.action,

                        symbol:
                            this.form.symbol,

                        amount:
                            this.form.action === 'remove'
                                ? null
                                : Number(
                                    this.form.amount
                                ),
                    });

                    this.form.amount = null;

                    await this.runSimulation();
                },

                async removeChange(index) {
                    this.changes.splice(
                        index,
                        1
                    );

                    if (
                        this.changes.length === 0
                    ) {
                        this.resetResults();

                        return;
                    }

                    await this.runSimulation();
                },

                reset() {
                    this.changes = [];

                    this.error = null;

                    this.form.amount = null;

                    this.resetResults();
                },

                resetResults() {
                    this.comparisonHoldings =
                        originalHoldings;

                    this.simulatedTotal =
                        initialTotal;

                    this.simulatedCash =
                        initialCash;

                    this.impact = {
                        largestPosition: null,
                        cash: null,
                        holdingsCount: null,
                        fundExpenses: null,
                        concentration: null,
                        summary: [],
                    };

                    this.analytics = {
                        current: null,
                        simulated: null,
                    };

                    this.productionAnalytics = {
                        current: null,
                        simulated: null,
                    };
                },

                updateImpact(comparison) {
                    this.impact = {
                        largestPosition:
                            comparison?.largest_position ?? null,

                        cash:
                            comparison?.cash ?? null,

                        holdingsCount:
                            comparison?.holdings_count ?? null,

                        fundExpenses:
                            comparison?.fund_expenses ?? null,

                        concentration:
                            comparison?.concentration ?? null,

                        summary:
                            comparison?.impact_summary ?? [],
                    };
                },

                updateAnalytics(analytics) {
                    this.analytics = {
                        current:
                            analytics?.current ?? null,

                        simulated:
                            analytics?.simulated ?? null,
                    };
                },

                updateProductionAnalytics(analytics) {
                    this.productionAnalytics = {
                        current:
                            analytics?.current ?? null,

                        simulated:
                            analytics?.simulated ?? null,
                    };
                },

                async runSimulation() {
                    this.loading = true;

                    this.error = null;

                    try {
                        const csrfToken =
                            this.csrfToken();

                        if (!csrfToken) {
                            throw new Error(
                                'CSRF token is missing.'
                            );
                        }

                        const response =
                            await fetch(
                                @json(
                                    route(
                                        'what-if.simulate'
                                    )
                                ),
                                {
                                    method: 'POST',

                                    headers: {
                                        'Content-Type':
                                            'application/json',

                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            csrfToken,
                                    },

                                    body:
                                        JSON.stringify({
                                            changes:
                                                this.changes,
                                        }),
                                }
                            );

                        const data =
                            await response.json();

                        if (
                            !response.ok
                            || !data.success
                        ) {
                            throw new Error(
                                data.message
                                || 'Unable to run simulation.'
                            );
                        }

                        this.comparisonHoldings =
                            data.comparison
                                ?.holdings
                            ?? [];

                        this.simulatedTotal =
                            data.simulated
                                ?.total_value
                            ?? initialTotal;

                        this.simulatedCash =
                            data.simulated
                                ?.cash
                            ?? initialCash;

                        this.updateImpact(
                            data.comparison
                        );

                        this.updateAnalytics(
                            data.analytics
                        );

                        this.updateProductionAnalytics(
                            data.production_analytics
                        );
                    } catch (error) {
                        console.error(
                            error
                        );

                        this.error =
                            error?.message
                            || 'Unable to run simulation.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>