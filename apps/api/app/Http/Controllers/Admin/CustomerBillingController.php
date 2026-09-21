<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAuditLog;
use App\Models\User;
use App\Services\Billing\StripeCustomerBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerBillingController extends Controller
{
    public function show(
        User $customer,
        StripeCustomerBillingService $billing,
    ): View {
        abort_if($customer->isStaff(), 404);

        return view('admin.customers.billing', [
            'customer' => $customer,
            'billing' => $billing->details($customer),
        ]);
    }

    public function refresh(
        Request $request,
        User $customer,
        StripeCustomerBillingService $billing,
    ): RedirectResponse {
        abort_if($customer->isStaff(), 404);

        $billing->forget($customer);
        $result = $billing->details($customer);

        StaffAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'customer_user_id' => $customer->id,
            'event' => 'customer.billing.refreshed',
            'route_name' => $request->route()?->getName(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['source' => $result['source']],
        ]);

        return back()->with(
            $result['available'] ? 'success' : 'warning',
            $result['available']
                ? 'Customer billing was refreshed from Stripe.'
                : 'Stripe could not be reached. Local billing records are displayed.',
        );
    }
}
