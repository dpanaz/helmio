<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Billing\BillingPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function edit(BillingPlanService $plans): View
    {
        return view('admin.pricing.edit', [
            'monthlyAmount' => $plans->amount('monthly'),
            'annualAmount' => $plans->amount('annual'),
            'monthlyPriceId' => $plans->priceId('monthly'),
            'annualPriceId' => $plans->priceId('annual'),
        ]);
    }

    public function update(Request $request, BillingPlanService $plans): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_amount' => ['required', 'numeric', 'min:1', 'max:9999.99'],
            'annual_amount' => ['required', 'numeric', 'min:1', 'max:99999.99'],
            'monthly_price_id' => ['required', 'string', 'starts_with:price_', 'max:255'],
            'annual_price_id' => ['required', 'string', 'starts_with:price_', 'max:255'],
        ]);

        $plans->update([
            'monthly_amount' => number_format((float) $validated['monthly_amount'], 2, '.', ''),
            'annual_amount' => number_format((float) $validated['annual_amount'], 2, '.', ''),
            'monthly_price_id' => $validated['monthly_price_id'],
            'annual_price_id' => $validated['annual_price_id'],
        ]);

        return back()->with('success', 'Subscription pricing was updated.');
    }
}
