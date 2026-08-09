{{-- resources/views/components/analytics/status-badge.blade.php --}}

@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'good' =>
            'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

        'warning' =>
            'border-amber-500/20 bg-amber-500/10 text-amber-300',

        'danger' =>
            'border-red-500/20 bg-red-500/10 text-red-300',

        'info' =>
            'border-blue-500/20 bg-blue-500/10 text-blue-300',

        'ai' =>
            'border-violet-500/20 bg-violet-500/10 text-violet-300',

        default =>
            'border-slate-700 bg-slate-800 text-slate-300',
    };
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold',
        $classes,
    ]) }}
>
    {{ $slot }}
</span>