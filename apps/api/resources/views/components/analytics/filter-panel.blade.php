@props([
    'title' => 'Analysis controls',
    'subtitle' => null,
])

<section
    {{ $attributes->class([
        'rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-black/10',
    ]) }}
>
    @if ($title || $subtitle)
        <div class="mb-5">
            @if ($title)
                <p class="text-sm font-semibold text-white">
                    {{ $title }}
                </p>
            @endif

            @if ($subtitle)
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>