<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffAuditController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'actor' => ['nullable', 'integer', 'exists:users,id'],
            'customer' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = StaffAuditLog::query()
            ->with(['actor', 'customer'])
            ->when(
                $filters['actor'] ?? null,
                fn ($query, $actor) => $query->where('actor_user_id', $actor),
            )
            ->when(
                $filters['event'] ?? null,
                fn ($query, $event) => $query->where('event', 'like', '%'.$event.'%'),
            )
            ->when(
                $filters['customer'] ?? null,
                fn ($query, $customer) => $query->whereHas(
                    'customer',
                    fn ($query) => $query->where(
                        fn ($query) => $query
                            ->where('name', 'like', '%'.$customer.'%')
                            ->orWhere('email', 'like', '%'.$customer.'%'),
                    ),
                ),
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $from) => $query->whereDate('created_at', '>=', $from),
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $to) => $query->whereDate('created_at', '<=', $to),
            )
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'staff' => User::query()
                ->whereHas('staffRoles')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'events' => StaffAuditLog::query()
                ->distinct()
                ->orderBy('event')
                ->pluck('event'),
        ]);
    }
}
