{{-- resources/views/components/analytics/panel.blade.php --}}

@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<section
    {{ $attributes->class([
        'overflow-hidden rounded-3xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-black/10',
    ]) }}
>
    @if ($title || $subtitle)
        <div class="border-b border-slate-800 px-6 py-5">
            @if ($title)
                <h3 class="text-base font-semibold text-white">
                    {{ $title }}
                </h3>
            @endif

            @if ($subtitle)
                <p class="mt-1 text-sm leading-6 text-slate-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    <div @class([
        'p-6' => $padding,
    ])>
        {{ $slot }}
    </div>
</section>