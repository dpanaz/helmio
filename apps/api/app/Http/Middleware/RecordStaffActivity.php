<?php

namespace App\Http\Middleware;

use App\Models\StaffAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStaffActivity
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $response = $next($request);
        $user = $request->user();

        if ($user && $user->isStaff()) {
            StaffAuditLog::query()->create([
                'actor_user_id' => $user->id,
                'event' => 'staff.portal.view',
                'route_name' => $request->route()?->getName(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'status_code' => $response->getStatusCode(),
                ],
            ]);
        }

        return $response;
    }
}
