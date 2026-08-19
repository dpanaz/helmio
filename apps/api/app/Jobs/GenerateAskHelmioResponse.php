<?php

namespace App\Jobs;

use App\Models\AskHelmioConversation;
use App\Models\AskHelmioMessage;
use App\Models\User;
use App\Services\AI\AskHelmioService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class GenerateAskHelmioResponse implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    public function __construct(
        public readonly int $userId,
        public readonly int $conversationId,
        public readonly int $userMessageId,
    ) {
        $this->onQueue('ask-helmio');
    }

    public function uniqueId(): string
    {
        return 'ask-helmio-message-'
            . $this->userMessageId;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'ask-helmio-conversation-'
                . $this->conversationId,
            ))
                ->releaseAfter(10)
                ->expireAfter(360),
        ];
    }

    public function handle(
        AskHelmioService $service,
    ): void {
        $user = User::query()->find(
            $this->userId,
        );

        $conversation =
            AskHelmioConversation::query()->find(
                $this->conversationId,
            );

        $userMessage =
            AskHelmioMessage::query()->find(
                $this->userMessageId,
            );

        if (
            $user === null
            || $conversation === null
            || $userMessage === null
        ) {
            return;
        }

        if (
            $conversation->user_id !== $user->id
            || $userMessage->user_id !== $user->id
            || $userMessage
                ->ask_helmio_conversation_id
                !== $conversation->id
        ) {
            return;
        }

        $service->generateResponse(
            $user,
            $userMessage,
            $conversation,
        );
    }

    public function backoff(): array
    {
        return [
            30,
            60,
            180,
        ];
    }
}