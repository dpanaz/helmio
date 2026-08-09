{{-- resources/views/components/analytics/methodology.blade.php --}}

@props([
    'title' => 'Methodology',
    'formulaVersion' => null,
])

<section
    {{ $attributes->class([
        'rounded-3xl border border-slate-800 bg-slate-900/80 p-7 shadow-xl shadow-black/10',
    ]) }}
>
    <div class="flex items-start gap-4">
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
                    d="M9 12h6m-6 4.5h6M7.5 3.75h7.629a2.25 2.25 0 0 1 1.591.659l2.871 2.871a2.25 2.25 0 0 1 .659 1.591V18a2.25 2.25 0 0 1-2.25 2.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z"
                />
            </svg>
        </div>

        <div class="min-w-0">
            <p
                class="text-xs font-semibold uppercase tracking-widest text-blue-400"
            >
                {{ $title }}
            </p>

            <div class="mt-3 text-sm leading-7 text-slate-400">
                {{ $slot }}
            </div>

            @if ($formulaVersion)
                <p class="mt-5 text-xs text-slate-600">
                    Formula version:
                    <span class="font-medium text-slate-500">
                        {{ $formulaVersion }}
                    </span>
                </p>
            @endif
        </div>
    </div>
</section>