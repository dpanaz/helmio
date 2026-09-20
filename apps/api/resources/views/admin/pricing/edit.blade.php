<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 border-b border-slate-800 pb-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Billing administration</p>
                <h1 class="mt-2 text-3xl font-semibold text-white">Subscription pricing</h1>
                <p class="mt-2 text-sm text-slate-400">Update the prices customers see and the matching Stripe Price IDs used at checkout.</p>
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.pricing.update') }}" class="rounded-2xl border border-slate-800 bg-slate-900 p-6 sm:p-8">
                @csrf
                @method('PUT')

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="monthly_amount" class="mb-2 block text-sm font-medium text-slate-300">Monthly display price</label>
                        <div class="relative"><span class="absolute left-4 top-3 text-slate-500">$</span><input id="monthly_amount" name="monthly_amount" type="number" step="0.01" min="1" value="{{ old('monthly_amount', number_format($monthlyAmount, 2, '.', '')) }}" required class="w-full rounded-xl border-slate-700 bg-slate-950 pl-8 text-white focus:border-blue-500 focus:ring-blue-500"></div>
                    </div>
                    <div>
                        <label for="annual_amount" class="mb-2 block text-sm font-medium text-slate-300">Annual display price</label>
                        <div class="relative"><span class="absolute left-4 top-3 text-slate-500">$</span><input id="annual_amount" name="annual_amount" type="number" step="0.01" min="1" value="{{ old('annual_amount', number_format($annualAmount, 2, '.', '')) }}" required class="w-full rounded-xl border-slate-700 bg-slate-950 pl-8 text-white focus:border-blue-500 focus:ring-blue-500"></div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="monthly_price_id" class="mb-2 block text-sm font-medium text-slate-300">Stripe monthly Price ID</label>
                        <input id="monthly_price_id" name="monthly_price_id" value="{{ old('monthly_price_id', $monthlyPriceId) }}" required placeholder="price_..." class="w-full rounded-xl border-slate-700 bg-slate-950 font-mono text-sm text-white focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="annual_price_id" class="mb-2 block text-sm font-medium text-slate-300">Stripe annual Price ID</label>
                        <input id="annual_price_id" name="annual_price_id" value="{{ old('annual_price_id', $annualPriceId) }}" required placeholder="price_..." class="w-full rounded-xl border-slate-700 bg-slate-950 font-mono text-sm text-white focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm leading-6 text-amber-200">Stripe prices are immutable. Create the new monthly and annual recurring prices in Stripe first, then paste their Price IDs here. Existing subscribers remain on their original price.</div>

                <button class="mt-6 w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500">Save pricing</button>
            </form>
        </div>
    </div>
</x-app-layout>
