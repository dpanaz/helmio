<x-app-layout>
    @php
        $activeSubscription = collect($billing['subscriptions'])->first(
            fn (array $subscription): bool => in_array($subscription['status'], ['active', 'trialing', 'past_due'], true)
        );
        $renewalDate = $activeSubscription
            ? ($activeSubscription['cancel_at'] ?? $activeSubscription['current_period_ends_at'])
            : null;
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-5 border-b border-slate-800 pb-6 lg:flex-row lg:items-end lg:justify-between">
                <div><a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-semibold text-blue-400">← {{ $customer->name }}</a><p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Customer billing</p><h1 class="mt-2 text-3xl font-semibold text-white">{{ $customer->name }}</h1><p class="mt-2 text-sm text-slate-400">{{ $customer->email }}</p></div>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.customers.billing.refresh', $customer) }}">@csrf<button class="rounded-xl border border-blue-500/30 bg-blue-500/10 px-4 py-3 text-sm font-semibold text-blue-300 hover:bg-blue-500/20">Refresh Stripe</button></form>
                    @if ($billing['stripe_dashboard_url'])<a href="{{ $billing['stripe_dashboard_url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Open in Stripe ↗</a>@endif
                </div>
            </header>

            @if (session('success'))<div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
            @if (session('warning'))<div class="mb-6 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-300">{{ session('warning') }}</div>@endif
            @unless ($billing['available'])<div class="mb-6 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200"><strong>Local fallback active.</strong> {{ $billing['error'] }}</div>@endunless

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['Subscription', $activeSubscription ? ucfirst($activeSubscription['status']) : 'None'],
                    ['Plan', $activeSubscription ? ucfirst($activeSubscription['interval'] ?? 'Unknown') : '—'],
                    ['Renewal / end', $renewalDate ? \Illuminate\Support\Carbon::parse($renewalDate)->format('M j, Y') : '—'],
                    ['Payment method', $billing['payment_method'] ?? 'Not available'],
                    ['Customer balance', number_format($billing['balance'], 2).' '.$billing['currency']],
                ] as [$label, $value])
                    <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-3 text-xl font-semibold text-white">{{ $value }}</p></article>
                @endforeach
            </section>

            @if ($billing['delinquent'])<div class="mt-6 rounded-2xl border border-red-500/30 bg-red-500/10 p-5 text-sm font-semibold text-red-200">Stripe marks this customer as delinquent. Review the outstanding invoice and payment method.</div>@endif

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="border-b border-slate-800 px-6 py-5"><h2 class="text-lg font-semibold text-white">Subscriptions</h2><p class="mt-1 text-sm text-slate-400">Current and historical Stripe subscriptions.</p></div>
                <div class="divide-y divide-slate-800">
                    @forelse ($billing['subscriptions'] as $subscription)
                        <div class="grid gap-4 px-6 py-5 md:grid-cols-5 md:items-center"><div><p class="font-semibold text-white">{{ $subscription['plan_name'] }}</p><p class="mt-1 font-mono text-xs text-slate-600">{{ $subscription['price_id'] }}</p></div><div><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ in_array($subscription['status'], ['active', 'trialing'], true) ? 'bg-emerald-500/10 text-emerald-300' : ($subscription['status'] === 'past_due' ? 'bg-red-500/10 text-red-300' : 'bg-slate-800 text-slate-300') }}">{{ ucfirst($subscription['status']) }}</span></div><p class="text-sm text-slate-300">@if ($subscription['amount'] !== null){{ number_format($subscription['amount'], 2) }} {{ $subscription['currency'] }} / {{ $subscription['interval'] ?? 'period' }}@else Amount unavailable @endif</p><p class="text-sm text-slate-400">Trial ends<br><span class="text-white">{{ $subscription['trial_ends_at'] ? \Illuminate\Support\Carbon::parse($subscription['trial_ends_at'])->format('M j, Y') : '—' }}</span></p><p class="text-sm text-slate-400">{{ $subscription['cancel_at_period_end'] ? 'Cancels' : 'Renews' }}<br><span class="text-white">{{ ($subscription['cancel_at'] ?? $subscription['current_period_ends_at']) ? \Illuminate\Support\Carbon::parse($subscription['cancel_at'] ?? $subscription['current_period_ends_at'])->format('M j, Y') : '—' }}</span></p></div>
                    @empty<p class="px-6 py-12 text-center text-sm text-slate-500">No subscription records found.</p>@endforelse
                </div>
            </section>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_0.8fr]">
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="border-b border-slate-800 px-6 py-5"><h2 class="text-lg font-semibold text-white">Invoices and payments</h2></div>
                    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-800"><thead><tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"><th class="px-5 py-4">Invoice</th><th class="px-5 py-4">Date</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Paid</th><th class="px-5 py-4"></th></tr></thead><tbody class="divide-y divide-slate-800">
                        @forelse ($billing['invoices'] as $invoice)<tr><td class="px-5 py-4 text-sm font-semibold text-white">{{ $invoice['number'] ?: $invoice['id'] }}</td><td class="px-5 py-4 text-sm text-slate-400">{{ $invoice['created_at'] ? \Illuminate\Support\Carbon::parse($invoice['created_at'])->format('M j, Y') : '—' }}</td><td class="px-5 py-4 text-sm">{{ ucfirst($invoice['status']) }}</td><td class="px-5 py-4 text-sm">{{ number_format($invoice['amount_paid'], 2) }} {{ $invoice['currency'] }}</td><td class="px-5 py-4 text-right">@if ($invoice['hosted_url'])<a href="{{ $invoice['hosted_url'] }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-blue-400">View ↗</a>@endif</td></tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No Stripe invoices available.</td></tr>@endforelse
                    </tbody></table></div>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6"><h2 class="text-lg font-semibold text-white">Refunds</h2><div class="mt-5 space-y-3">@forelse ($billing['refunds'] as $refund)<div class="rounded-xl bg-slate-950/60 p-4"><div class="flex justify-between gap-3"><span class="font-semibold text-white">{{ number_format($refund['amount'], 2) }} {{ $refund['currency'] }}</span><span class="text-xs text-slate-500">{{ $refund['created_at'] ? \Illuminate\Support\Carbon::parse($refund['created_at'])->format('M j, Y') : '' }}</span></div><p class="mt-2 text-xs text-slate-400">{{ ucfirst($refund['status']) }}{{ $refund['reason'] ? ' · '.str_replace('_', ' ', $refund['reason']) : '' }}</p></div>@empty<p class="text-sm text-slate-500">No refunds found.</p>@endforelse</div></section>
            </div>

            <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900 p-6"><div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold text-white">Billing timeline</h2><p class="mt-1 text-sm text-slate-400">Subscription, invoice, payment, and refund events.</p></div><p class="text-xs text-slate-500">Refreshed {{ \Illuminate\Support\Carbon::parse($billing['refreshed_at'])->diffForHumans() }}</p></div><div class="mt-6 space-y-4">@forelse ($billing['timeline'] as $event)<div class="flex gap-4"><span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $event['type'] === 'refund' ? 'bg-amber-400' : ($event['type'] === 'invoice' ? 'bg-blue-400' : 'bg-emerald-400') }}"></span><div><p class="text-sm font-semibold text-white">{{ $event['title'] }}</p><p class="mt-1 text-xs text-slate-400">{{ $event['detail'] }} · {{ $event['occurred_at'] ? \Illuminate\Support\Carbon::parse($event['occurred_at'])->diffForHumans() : 'Date unavailable' }}</p></div></div>@empty<p class="text-sm text-slate-500">No live Stripe billing events available.</p>@endforelse</div></section>
        </div>
    </div>
</x-app-layout>
