@extends('layouts.marketing')

@section('title', $page['title'])
@section('meta_description', $page['meta_description'])
@section('canonical', url($page['canonical']))

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page['title'],
    'description' => $page['meta_description'],
    'url' => url($page['canonical']),
    'publisher' => ['@type' => 'Organization', 'name' => 'Helmio', 'url' => url('/')],
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<section class="bg-white">
    <div class="mx-auto max-w-7xl px-6 py-20 sm:py-24 lg:px-8">
        <div class="max-w-4xl">
            <div class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-medium text-slate-700">{{ $page['eyebrow'] }}</div>
            <h1 class="mt-6 text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl">{{ $page['headline'] }}</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-600 sm:text-xl">{{ $page['subheadline'] }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $page['primary_url'] ?? route('register') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white">{{ $page['primary_cta'] }}</a>
                @if (!empty($page['secondary_cta']))
                    <a href="{{ url($page['secondary_url']) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold">{{ $page['secondary_cta'] }}</a>
                @endif
            </div>
            <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500">
                <span>Read-only monitoring</span><span>No trading authority</span><span>Independent analytics</span>
            </div>
        </div>
    </div>
</section>

<section class="border-y border-slate-200 bg-slate-50">
    <div class="mx-auto max-w-5xl px-6 py-14 lg:px-8">
        <p class="text-lg leading-8 text-slate-700">{{ $page['intro'] }}</p>
    </div>
</section>

<section class="bg-white">
    <div class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
        <div class="space-y-16">
            @foreach($page['sections'] as $section)
                <section>
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $section['heading'] }}</h2>
                    <p class="mt-4 leading-7 text-slate-600">{{ $section['body'] }}</p>
                    @if(!empty($section['items']))
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach($section['items'] as $item)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-sm leading-6 text-slate-700">{{ $item }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</section>

@if(!empty($page['faq']))
<section class="border-t border-slate-200 bg-slate-50">
    <div class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
        <h2 class="text-3xl font-semibold tracking-tight">Frequently asked questions</h2>
        <div class="mt-8 divide-y divide-slate-200 border-y border-slate-200">
            @foreach($page['faq'] as $item)
                <details class="py-5">
                    <summary class="cursor-pointer font-medium">{{ $item['q'] }}</summary>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="bg-slate-950 text-white">
    <div class="mx-auto max-w-5xl px-6 py-16 lg:px-8">
        <h2 class="text-3xl font-semibold tracking-tight">Know what is happening with your money.</h2>
        <p class="mt-4 max-w-2xl leading-7 text-slate-300">Helmio monitors costs, performance, risk, diversification, trading activity, and tax efficiency so you can see what deserves attention.</p>
        <a href="{{ route('register') }}" class="mt-7 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-950">Get started</a>
    </div>
</section>
@endsection
