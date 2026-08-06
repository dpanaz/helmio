<?php

namespace App\Http\Controllers;

use App\Models\AskHelmioConversation;
use App\Services\AI\AskHelmioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AskHelmioController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $conversations = $this->conversationQuery(
            $request,
        )->get();

        $conversation = $conversations->first();

        if ($conversation !== null) {
            $conversation->load([
                'messages' => fn ($query) =>
                    $query->orderBy('id'),
            ]);
        }

        return view('ask-helmio.index', [
            'conversations' => $conversations,
            'conversation' => $conversation,
        ]);
    }

    public function show(
        Request $request,
        AskHelmioConversation $askHelmioConversation,
    ): View {
        $this->authorizeConversation(
            $request,
            $askHelmioConversation,
        );

        $askHelmioConversation->load([
            'messages' => fn ($query) =>
                $query->orderBy('id'),
        ]);

        return view('ask-helmio.index', [
            'conversations' =>
                $this->conversationQuery(
                    $request,
                )->get(),

            'conversation' =>
                $askHelmioConversation,
        ]);
    }

    public function store(
        Request $request,
        AskHelmioService $askHelmioService,
    ): RedirectResponse {
        $validated = $request->validate([
            'question' => [
                'required',
                'string',
                'min:2',
                'max:2000',
            ],

            'conversation_id' => [
                'nullable',
                'integer',
                'exists:ask_helmio_conversations,id',
            ],
        ]);

        $conversation = null;

        if (! empty($validated['conversation_id'])) {
            $conversation =
                AskHelmioConversation::query()
                    ->where(
                        'user_id',
                        $request->user()->id,
                    )
                    ->findOrFail(
                        $validated['conversation_id'],
                    );
        }

        $message = $askHelmioService->ask(
            $request->user(),
            $validated['question'],
            $conversation,
        );

        return redirect()
            ->route(
                'ask-helmio.show',
                $message->conversation,
            );
    }

    public function create(
        Request $request,
    ): View {
        return view('ask-helmio.index', [
            'conversations' =>
                $this->conversationQuery(
                    $request,
                )->get(),

            'conversation' => null,
        ]);
    }

    public function archive(
        Request $request,
        AskHelmioConversation $askHelmioConversation,
    ): RedirectResponse {
        $this->authorizeConversation(
            $request,
            $askHelmioConversation,
        );

        $askHelmioConversation->update([
            'status' =>
                AskHelmioConversation::STATUS_ARCHIVED,
        ]);

        return redirect()
            ->route('ask-helmio.index')
            ->with(
                'success',
                'Conversation archived.',
            );
    }

    private function conversationQuery(
        Request $request,
    ) {
        return AskHelmioConversation::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->where(
                'status',
                AskHelmioConversation::STATUS_ACTIVE,
            )
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    private function authorizeConversation(
        Request $request,
        AskHelmioConversation $conversation,
    ): void {
        abort_unless(
            $conversation->user_id
                === $request->user()->id,
            403,
        );
    }
}