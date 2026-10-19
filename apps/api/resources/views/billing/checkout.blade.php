<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Unlock your Helmio review</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <section class="mx-auto max-w-4xl space-y-5 rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-10">
            <p class="text-sm font-semibold text-blue-400">Step 3 of 3 · Payment</p>
            <h1 class="text-3xl font-semibold text-white">Your next step to a clearer portfolio.</h1>
            <p class="text-slate-400">Your accounts are linked. Complete secure checkout below to start your subscription and unlock your review.</p>
            <div id="checkout-message" role="status" class="text-slate-300">Loading secure payment form…</div>
            <div id="embedded-checkout"></div>
            <a href="{{ route('billing.pricing') }}" class="inline-block text-blue-300 underline">Change plan</a>
        </section>
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        (async () => {
            const message = document.getElementById('checkout-message');
            try {
                const stripe = Stripe(@js($stripeKey));
                const checkout = await stripe.initEmbeddedCheckout({
                    clientSecret: @js($clientSecret),
                    onComplete: () => window.location.assign(@js(route('billing.success'))),
                });
                checkout.mount('#embedded-checkout');
                message.hidden = true;
            } catch (error) {
                message.textContent = 'We could not load the payment form. Refresh this page to try again, or go back to choose your plan.';
                message.setAttribute('role', 'alert');
            }
        })();
    </script>
</x-app-layout>
