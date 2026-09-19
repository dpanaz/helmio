<?php
namespace App\Http\Middleware;

use App\Models\StaffAuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ReadOnlyCustomerPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/*')) {
            return $next($request);
        }

        $staffId = $request->session()->get('customer_preview.staff_user_id');
        $customerId = $request->session()->get('customer_preview.customer_user_id');
        $expiresAt = $request->session()->get('customer_preview.expires_at');

        if (! $staffId || ! $customerId || ! $expiresAt) {
            return $next($request);
        }

        $staff = $request->user();

        if (! $staff || $staff->id !== (int) $staffId || ! $staff->isStaff()) {
            $this->clear($request);
            return $next($request);
        }

        if (now()->timestamp >= (int) $expiresAt) {
            $this->clear($request);
            return redirect()->route('admin.dashboard')->with('status', 'The customer preview expired.');
        }

        abort_unless(
            in_array($request->method(), ['GET', 'HEAD'], true),
            405,
            'Customer preview is read-only.',
        );

        $customer = User::query()->find($customerId);

        if (! $customer || $customer->isStaff()) {
            $this->clear($request);
            return redirect()->route('admin.dashboard');
        }

        Auth::guard()->setUser($customer);
        $request->setUserResolver(fn (): User => $customer);

        View::share([
            'customerPreviewActive' => true,
            'customerPreviewStaff' => $staff,
            'customerPreviewCustomer' => $customer,
        ]);

        $response = $next($request);

        StaffAuditLog::query()->create([
            'actor_user_id' => $staff->id,
            'customer_user_id' => $customer->id,
            'event' => 'customer.preview.page_viewed',
            'route_name' => $request->route()?->getName(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['status_code' => $response->getStatusCode()],
        ]);

        return $response;
    }

    private function clear(Request $request): void
    {
        $request->session()->forget('customer_preview');
    }
}
