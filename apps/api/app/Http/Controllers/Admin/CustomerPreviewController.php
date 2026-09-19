<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerPreviewController extends Controller
{
    public function store(Request $request, User $customer): RedirectResponse
    {
        abort_if($customer->isStaff(), 404);
        $staff = $request->user();

        $request->session()->put('customer_preview', [
            'staff_user_id' => $staff->id,
            'customer_user_id' => $customer->id,
            'expires_at' => now()->addMinutes(20)->timestamp,
        ]);

        $this->audit($request, 'customer.preview.started', $customer->id);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $customerId = $request->session()->get('customer_preview.customer_user_id');

        if ($customerId) {
            $this->audit($request, 'customer.preview.ended', (int) $customerId);
        }

        $request->session()->forget('customer_preview');

        return $customerId
            ? redirect()->route('admin.customers.show', $customerId)
            : redirect()->route('admin.dashboard');
    }

    private function audit(Request $request, string $event, int $customerId): void
    {
        StaffAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'customer_user_id' => $customerId,
            'event' => $event,
            'route_name' => $request->route()?->getName(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
