{{-- resources/views/components/analytics/message-card.blade.php --}}

@props([
    'tone' => 'info',
    'title' => null,
])

@php
    $classes = match ($tone) {
        'good' =>
            'border-emerald-500/20 bg-emerald-500/[0.07]',

        'warning' =>
            'border-amber-500/20 bg-amber-500/[0.07]',

        'danger' =>
            'border-red-500/20 bg-red-500/[0.07]',

        'ai' =>
            'border-violet-500/20 bg-violet-500/[0.07]',

        default =>
            'border-blue-500/20 bg-blue-500/[0.07]',
    };

    $titleClasses = match ($tone) {
        'good' => 'text-emerald-300',
        'warning' => 'text-amber-300',
        'danger' => 'text-red-300',
        'ai' => 'text-violet-300',
        default => 'text-blue-300',
    };
@endphp

<div
    {{ $attributes->class([
        'rounded-2xl border px-5 py-4',
        $classes,
    ]) }}
>
    @if ($title)
        <p class="font-semibold {{ $titleClasses }}">
            {{ $title }}
        </p>
    @endif

    <div class="mt-1 text-sm leading-6 text-slate-400">
        {{ $slot }}
    </div>
</div>