<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAskHelmioResponse;
use App\Models\AskHelmioConversation;
use App\Models\AskHelmioMessage;
use App\Services\AI\AskHelmioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAskHelmioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = AskHelmioConversation::query()
            ->where('user_id', $request->user()->id)
            ->where(
                'status',
                AskHelmioConversation::STATUS_ACTIVE,
            )
            ->with([
                'messages' => function ($query): void {
                    $query->orderBy('id');
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json([
            'conversations' => $conversations
                ->map(
                    fn (
                        AskHelmioConversation $conversation,
                    ): array => $this->conversationData(
                        $conversation,
                    ),
                )
                ->values(),
        ]);
    }

    public function show(
        Request $request,
        AskHelmioConversation $conversation,
    ): JsonResponse {
        $this->authorizeConversation(
            $request,
            $conversation,
        );

        $conversation->load([
            'messages' => function ($query): void {
                $query->orderBy('id');
            },
        ]);

        return response()->json([
            'conversation' =>
                $this->conversationData(
                    $conversation,
                ),
        ]);
    }

    public function ask(
        Request $request,
        AskHelmioService $askHelmioService,
    ): JsonResponse {
        $validated = $request->validate([
            'question' => [
                'required',
                'string',
                'max:2000',
            ],

            'conversation_id' => [
                'nullable',
                'integer',
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
            $askHelmioService
                ->submitQuestion(
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

        return response()->json([
            'status' =>
                'generating',

            'conversation_id' =>
                $conversation->id,

            'question_message_id' =>
                $userMessage->id,

            'message' => [
                'id' =>
                    $userMessage->id,

                'role' =>
                    $userMessage->role,

                'content' =>
                    $userMessage->content,

                'status' =>
                    $userMessage->status,

                'generated_at' =>
                    $userMessage
                        ->generated_at
                        ?->toIso8601String(),
            ],
        ], 202);
    }

    public function status(
        Request $request,
        AskHelmioConversation $conversation,
    ): JsonResponse {
        $this->authorizeConversation(
            $request,
            $conversation,
        );

        $questionMessageId =
            max(
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
                    $conversation->id,
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
                    ? $this->messageData(
                        $assistantMessage,
                    )
                    : null,
        ]);
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

    private function conversationData(
        AskHelmioConversation $conversation,
    ): array {
        return [
            'id' =>
                $conversation->id,

            'title' =>
                $conversation->title,

            'status' =>
                $conversation->status,

            'last_message_at' =>
                $conversation
                    ->last_message_at
                    ?->toIso8601String(),

            'messages' =>
                $conversation
                    ->messages
                    ->map(
                        fn (
                            AskHelmioMessage $message,
                        ): array =>
                            $this->messageData(
                                $message,
                            ),
                    )
                    ->values()
                    ->all(),
        ];
    }

    private function messageData(
        AskHelmioMessage $message,
    ): array {
        return [
            'id' =>
                $message->id,

            'role' =>
                $message->role,

            'content' =>
                $message->content,

            'status' =>
                $message->status,

            'confidence' =>
                $message->confidence,

            'citations' =>
                $message->citations ?? [],

            'limitations' =>
                $message->limitations ?? [],

            'generated_at' =>
                $message
                    ->generated_at
                    ?->toIso8601String(),
        ];
    }
}
