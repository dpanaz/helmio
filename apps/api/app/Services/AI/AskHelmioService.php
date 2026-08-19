<?php

namespace App\Services\AI;

use App\Contracts\AI\PortfolioChatProviderInterface;
use App\Models\AskHelmioConversation;
use App\Models\AskHelmioMessage;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class AskHelmioService
{
    public function __construct(
        private readonly AskHelmioContextService $contextService,
        private readonly PortfolioChatProviderInterface $provider,
    ) {
    }

    /**
     * Save the user's question quickly so the web request can return
     * immediately. AI generation happens later in the queue.
     */
    public function submitQuestion(
        User $user,
        string $question,
        ?AskHelmioConversation $conversation = null,
    ): AskHelmioMessage {
        $question = trim($question);

        if ($question === '') {
            throw new InvalidArgumentException(
                'A question is required.',
            );
        }

        $conversation ??=
            AskHelmioConversation::query()->create([
                'user_id' => $user->id,
                'title' => Str::limit($question, 80),
                'status' =>
                    AskHelmioConversation::STATUS_ACTIVE,
                'last_message_at' => now(),
                'metadata' => [
                    'created_by' => 'ask_helmio',
                ],
            ]);

        abort_unless(
            $conversation->user_id === $user->id,
            403,
        );

        $userMessage = AskHelmioMessage::query()->create([
            'ask_helmio_conversation_id' =>
                $conversation->id,
            'user_id' => $user->id,
            'role' => AskHelmioMessage::ROLE_USER,
            'content' => $question,
            'status' =>
                AskHelmioMessage::STATUS_COMPLETED,
            'generated_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return $userMessage->load('conversation');
    }

    /**
     * Generate the assistant response for a previously saved user message.
     * This method is intended to run inside a queue worker.
     */
    public function generateResponse(
        User $user,
        AskHelmioMessage $userMessage,
        AskHelmioConversation $conversation,
    ): AskHelmioMessage {
        abort_unless(
            $conversation->user_id === $user->id
            && $userMessage->user_id === $user->id
            && $userMessage->ask_helmio_conversation_id
                === $conversation->id
            && $userMessage->role
                === AskHelmioMessage::ROLE_USER,
            403,
        );

        /*
         * Idempotency guard:
         * if an assistant response already exists after this question,
         * do not create a duplicate when a queued job is retried.
         */
        $existingResponse = AskHelmioMessage::query()
            ->where(
                'ask_helmio_conversation_id',
                $conversation->id,
            )
            ->where(
                'role',
                AskHelmioMessage::ROLE_ASSISTANT,
            )
            ->where('id', '>', $userMessage->id)
            ->orderBy('id')
            ->first();

        if ($existingResponse !== null) {
            return $existingResponse->load('conversation');
        }

        $question = trim(
            (string) $userMessage->content,
        );

        $context = $this->contextService->build(
            $user,
            $question,
        );

        $history = $conversation
            ->messages()
            ->where('id', '<', $userMessage->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->map(
                fn (
                    AskHelmioMessage $message,
                ): array => [
                    'role' => $message->role,
                    'content' => $message->content,
                ],
            )
            ->values()
            ->all();

        try {
            $response = $this->provider->answer(
                $question,
                $context,
                $history,
            );

            $assistantMessage =
                AskHelmioMessage::query()->create([
                    'ask_helmio_conversation_id' =>
                        $conversation->id,

                    'user_id' => $user->id,

                    'role' =>
                        AskHelmioMessage::ROLE_ASSISTANT,

                    'content' =>
                        $response['answer']
                        ?? 'No answer was generated.',

                    'provider' =>
                        $this->provider->providerName(),

                    'model' =>
                        $this->provider->modelName(),

                    'status' =>
                        AskHelmioMessage::STATUS_COMPLETED,

                    'confidence' =>
                        $response['confidence']
                        ?? null,

                    'citations' =>
                        $response['citations']
                        ?? [],

                    'limitations' =>
                        $response['limitations']
                        ?? [],

                    'context_snapshot' =>
                        $context,

                    'response_payload' => [
                        'confidence' =>
                            $response['confidence']
                            ?? null,

                        'provider_response_id' =>
                            $response[
                                'provider_response_id'
                            ] ?? null,

                        'total_tokens' =>
                            $response['total_tokens']
                            ?? null,
                    ],

                    'input_tokens' =>
                        $response['input_tokens']
                        ?? null,

                    'output_tokens' =>
                        $response['output_tokens']
                        ?? null,

                    'generated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            report($exception);

            $assistantMessage =
                AskHelmioMessage::query()->create([
                    'ask_helmio_conversation_id' =>
                        $conversation->id,

                    'user_id' => $user->id,

                    'role' =>
                        AskHelmioMessage::ROLE_ASSISTANT,

                    'content' =>
                        'Helmio could not answer that question.',

                    'provider' =>
                        $this->provider->providerName(),

                    'model' =>
                        $this->provider->modelName(),

                    'status' =>
                        AskHelmioMessage::STATUS_FAILED,

                    'confidence' => 'low',

                    'citations' => [],

                    'limitations' =>
                        $context['limitations'] ?? [],

                    'context_snapshot' =>
                        $context,

                    'response_payload' => [
                        'exception_class' =>
                            $exception::class,
                    ],

                    'generated_at' => now(),

                    'error_message' =>
                        $exception->getMessage(),
                ]);
        }

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return $assistantMessage->load('conversation');
    }

    /**
     * Backward-compatible synchronous entry point.
     * Existing callers can keep using ask(), while the controller uses
     * submitQuestion() + a queued GenerateAskHelmioResponse job.
     */
    public function ask(
        User $user,
        string $question,
        ?AskHelmioConversation $conversation = null,
    ): AskHelmioMessage {
        $userMessage = $this->submitQuestion(
            $user,
            $question,
            $conversation,
        );

        return $this->generateResponse(
            $user,
            $userMessage,
            $userMessage->conversation,
        );
    }
}