<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Billing\StripeBusinessMetricsService;
use Illuminate\Http\RedirectResponse;

class StripeMetricsController extends Controller
{
    public function refresh(StripeBusinessMetricsService $metrics): RedirectResponse
    {
        $metrics->forget();
        $result = $metrics->metrics();

        return back()->with(
            $result['available'] ? 'success' : 'warning',
            $result['available']
                ? 'Stripe metrics were refreshed.'
                : 'Stripe could not be reached. Helmio is showing local billing records.',
        );
    }
}
