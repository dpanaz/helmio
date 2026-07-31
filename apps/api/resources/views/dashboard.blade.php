<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-600">Account overview</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}</h1>
                <p class="mt-2 text-slate-600">Your independent investment-monitoring dashboard is ready.</p>
            </div>
            <button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Connect an account</button>
        </div>

        <section class="grid gap-6 lg:grid-cols-[1.1fr_2fr]">
            <div class="overflow-hidden rounded-3xl bg-slate-950 p-7 text-white shadow-xl shadow-slate-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-300">Helm Score</p>
                        <p class="mt-3 text-6xl font-extrabold tracking-tight">—</p>
                    </div>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-200">Awaiting data</span>
                </div>
                <p class="mt-8 max-w-sm text-sm leading-6 text-slate-300">Connect a brokerage account to calculate cost, performance, diversification, trading, risk, tax-efficiency, and compliance-indicator scores.</p>
                <div class="mt-7 h-2 overflow-hidden rounded-full bg-white/10"><div class="h-full w-1/6 rounded-full bg-blue-500"></div></div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Portfolio value', '$0.00', 'No accounts connected'],
                    ['Annual fees', '$0.00', 'Estimated all-in cost'],
                    ['Net return', '—', 'Trailing 12 months'],
                    ['Open alerts', '0', 'No concerns detected'],
                ] as [$label, $value, $note])
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-2xl font-bold tracking-tight text-slate-950">{{ $value }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $note }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div><h2 class="text-lg font-bold text-slate-950">Portfolio performance</h2><p class="mt-1 text-sm text-slate-500">Performance will appear after your first account sync.</p></div>
                    <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">12 months</span>
                </div>
                <div class="mt-8 flex h-56 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50">
                    <div class="text-center"><p class="font-semibold text-slate-700">No portfolio data yet</p><p class="mt-2 text-sm text-slate-500">Connect an account to begin monitoring.</p></div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-950">Monitoring areas</h2>
                <div class="mt-5 space-y-4">
                    @foreach (['Fees and expenses','Trading activity','Performance','Diversification','Risk indicators'] as $item)
                        <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span><span class="text-sm font-medium text-slate-700">{{ $item }}</span></div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
