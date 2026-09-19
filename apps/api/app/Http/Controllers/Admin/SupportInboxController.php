<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportInboxController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $channel = $request->string('channel')->toString();

        $conversations = SupportConversation::query()
            ->with(['customer', 'assignee'])
            ->withCount('messages')
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status),
            )
            ->when(
                $channel !== '',
                fn ($query) => $query->where('channel', $channel),
            )
            ->orderByRaw(
                "CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END",
            )
            ->orderByDesc('last_message_at')
            ->paginate(30)
            ->withQueryString();

        $counts = SupportConversation::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.support.index', compact(
            'conversations',
            'counts',
            'status',
            'channel',
        ));
    }

    public function show(
        SupportConversation $conversation,
    ): View {
        $conversation->update([
            'staff_last_read_at' => now(),
        ]);

        $conversation->load([
            'customer',
            'assignee',
            'messages.sender',
        ]);

        $staff = User::query()
            ->whereHas('staffRoles')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.support.show', compact(
            'conversation',
            'staff',
        ));
    }

    public function update(
        Request $request,
        SupportConversation $conversation,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'open',
                    'pending',
                    'waiting_customer',
                    'resolved',
                    'closed',
                ]),
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'normal', 'high', 'urgent']),
            ],
            'assigned_to_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        if ($validated['assigned_to_user_id'] ?? null) {
            abort_unless(
                User::query()
                    ->findOrFail($validated['assigned_to_user_id'])
                    ->isStaff(),
                422,
            );
        }

        $conversation->update([
            ...$validated,
            'resolved_at' => in_array(
                $validated['status'],
                ['resolved', 'closed'],
                true,
            ) ? ($conversation->resolved_at ?? now()) : null,
        ]);

        return back()->with('status', 'Conversation updated.');
    }

    public function reply(
        Request $request,
        SupportConversation $conversation,
    ): RedirectResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $internal = (bool) ($validated['is_internal'] ?? false);

        $conversation->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => 'staff',
            'body' => $validated['message'],
            'is_internal' => $internal,
        ]);

        $updates = [
            'assigned_to_user_id' =>
                $conversation->assigned_to_user_id
                ?? $request->user()->id,
            'last_message_at' => now(),
            'staff_last_read_at' => now(),
        ];

        if (! $internal) {
            $updates['status'] =
                SupportConversation::STATUS_WAITING_CUSTOMER;
        }

        $conversation->update($updates);

        return back()->with(
            'status',
            $internal
                ? 'Internal note added.'
                : 'Reply sent to the customer.',
        );
    }
}
