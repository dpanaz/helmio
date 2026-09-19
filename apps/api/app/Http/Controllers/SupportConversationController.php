<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportConversationController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = SupportConversation::query()
            ->where('user_id', $request->user()->id)
            ->with('assignee')
            ->withCount([
                'messages as customer_visible_messages_count' =>
                    fn ($query) => $query->where('is_internal', false),
            ])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('support.index', compact('conversations'));
    }

    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => [
                'required',
                Rule::in([
                    SupportConversation::CHANNEL_TICKET,
                    SupportConversation::CHANNEL_LIVE_CHAT,
                ]),
            ],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $conversation = DB::transaction(function () use ($request, $validated): SupportConversation {
            $conversation = SupportConversation::query()->create([
                'user_id' => $request->user()->id,
                'channel' => $validated['channel'],
                'status' => SupportConversation::STATUS_OPEN,
                'priority' => 'normal',
                'subject' => $validated['subject'],
                'last_message_at' => now(),
                'customer_last_read_at' => now(),
            ]);

            $conversation->messages()->create([
                'sender_user_id' => $request->user()->id,
                'sender_type' => 'customer',
                'body' => $validated['message'],
                'is_internal' => false,
            ]);

            return $conversation;
        });

        return redirect()
            ->route('support.show', $conversation)
            ->with('status', 'Your message was sent to Helmio support.');
    }

    public function show(
        Request $request,
        SupportConversation $conversation,
    ): View {
        $this->authorizeCustomer($request, $conversation);

        $conversation->update([
            'customer_last_read_at' => now(),
        ]);

        $conversation->load([
            'messages' => fn ($query) =>
                $query
                    ->where('is_internal', false)
                    ->with('sender')
                    ->oldest(),
            'assignee',
        ]);

        return view('support.show', compact('conversation'));
    }

    public function reply(
        Request $request,
        SupportConversation $conversation,
    ): RedirectResponse {
        $this->authorizeCustomer($request, $conversation);

        abort_if(
            $conversation->status === SupportConversation::STATUS_CLOSED,
            422,
            'This conversation is closed.',
        );

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $conversation->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => 'customer',
            'body' => $validated['message'],
            'is_internal' => false,
        ]);

        $conversation->update([
            'status' => SupportConversation::STATUS_OPEN,
            'last_message_at' => now(),
            'customer_last_read_at' => now(),
        ]);

        return back()->with('status', 'Reply sent.');
    }

    public function messages(
        Request $request,
        SupportConversation $conversation,
    ): JsonResponse {
        $this->authorizeCustomer($request, $conversation);
        $after = max(0, $request->integer('after'));

        $messages = $conversation->messages()
            ->where('is_internal', false)
            ->where('id', '>', $after)
            ->with('sender:id,name')
            ->oldest()
            ->get()
            ->map(fn (SupportMessage $message): array => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender?->name
                    ?? ($message->sender_type === 'staff' ? 'Helmio Support' : 'Customer'),
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        if ($messages->isNotEmpty()) {
            $conversation->update([
                'customer_last_read_at' => now(),
            ]);
        }

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->fresh()->status,
        ]);
    }

    private function authorizeCustomer(
        Request $request,
        SupportConversation $conversation,
    ): void {
        abort_unless(
            $conversation->user_id === $request->user()->id,
            404,
        );
    }
}
