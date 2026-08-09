{{-- resources/views/components/analytics/metric-card.blade.php --}}

@props([
    'label',
    'value' => '—',
    'description' => null,
    'tone' => 'default',
])

@php
    $toneClasses = match ($tone) {
        'good' =>
            'border-emerald-500/20 bg-emerald-500/[0.06]',

        'warning' =>
            'border-amber-500/20 bg-amber-500/[0.06]',

        'danger' =>
            'border-red-500/20 bg-red-500/[0.06]',

        'ai' =>
            'border-violet-500/20 bg-violet-500/[0.06]',

        default =>
            'border-slate-800 bg-slate-900/80',
    };

    $valueClasses = match ($tone) {
        'good' => 'text-emerald-300',
        'warning' => 'text-amber-300',
        'danger' => 'text-red-300',
        'ai' => 'text-violet-300',
        default => 'text-white',
    };
@endphp

<article
    {{ $attributes->class([
        'rounded-2xl border p-5 shadow-lg shadow-black/5',
        $toneClasses,
    ]) }}
>
    <p class="text-sm font-medium text-slate-500">
        {{ $label }}
    </p>

    <p
        class="mt-3 text-3xl font-semibold tracking-tight {{ $valueClasses }}"
    >
        {{ $value }}
    </p>

    @if ($description)
        <p class="mt-2 text-xs leading-5 text-slate-600">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}
</article>