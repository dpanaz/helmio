<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAskHelmioResponse;
use App\Models\AskHelmioConversation;
use App\Models\AskHelmioMessage;
use App\Services\AI\AskHelmioService;
use Illuminate\Http\JsonResponse;
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

        $userMessage =
            $askHelmioService->submitQuestion(
                $request->user(),
                $validated['question'],
                $conversation,
            );

        $conversation =
            $userMessage->conversation;

        GenerateAskHelmioResponse::dispatch(
            userId: $request->user()->id,
            conversationId: $conversation->id,
            userMessageId: $userMessage->id,
        );

        return redirect()
            ->route(
                'ask-helmio.show',
                [
                    'askHelmioConversation' =>
                        $conversation,

                    'generating' => 1,

                    'question_message_id' =>
                        $userMessage->id,
                ],
            );
    }

    public function status(
        Request $request,
        AskHelmioConversation $askHelmioConversation,
    ): JsonResponse {
        $this->authorizeConversation(
            $request,
            $askHelmioConversation,
        );

        $questionMessageId = max(
            0,
            (int) $request->query(
                'question_message_id',
                0,
            ),
        );

        $assistantMessage =
            AskHelmioMessage::query()
                ->where(
                    'ask_helmio_conversation_id',
                    $askHelmioConversation->id,
                )
                ->where(
                    'role',
                    AskHelmioMessage::ROLE_ASSISTANT,
                )
                ->where(
                    'id',
                    '>',
                    $questionMessageId,
                )
                ->orderBy('id')
                ->first();

        return response()->json([
            'finished' =>
                $assistantMessage !== null,

            'assistant_message' =>
                $assistantMessage
                    ? [
                        'id' =>
                            $assistantMessage->id,

                        'status' =>
                            $assistantMessage->status,

                        'generated_at' =>
                            $assistantMessage
                                ->generated_at
                                ?->toIso8601String(),

                        'show_url' =>
                            route(
                                'ask-helmio.show',
                                $askHelmioConversation,
                            ),
                    ]
                    : null,
        ]);
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