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

        /*
         * Build the context before calling the provider.
         *
         * The context service is now intentionally resilient: an unavailable
         * analytics category should be represented in data_availability rather
         * than causing the entire Ask Helmio request to fail.
         */
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

                    /*
                     * IMPORTANT:
                     *
                     * Low confidence is still a completed response.
                     * It is not a technical failure.
                     */
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

                        'fallback' => false,
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

            /*
             * A provider/API failure should not leave the user with a useless
             * "Helmio could not answer that question" message when Helmio
             * already has deterministic analytics in the context.
             *
             * Build a conservative, context-grounded fallback answer instead.
             */
            $fallback = $this->buildFallbackResponse(
                $question,
                $context,
            );

            $assistantMessage =
                AskHelmioMessage::query()->create([
                    'ask_helmio_conversation_id' =>
                        $conversation->id,

                    'user_id' => $user->id,

                    'role' =>
                        AskHelmioMessage::ROLE_ASSISTANT,

                    'content' =>
                        $fallback['answer'],

                    'provider' =>
                        $this->provider->providerName(),

                    'model' =>
                        $this->provider->modelName(),

                    /*
                     * The AI provider failed, but Helmio still returned a
                     * grounded answer from its own analytics. Treat the user
                     * response as completed while preserving the technical
                     * failure details in response_payload/error_message.
                     */
                    'status' =>
                        AskHelmioMessage::STATUS_COMPLETED,

                    'confidence' =>
                        $fallback['confidence'],

                    'citations' =>
                        $fallback['citations'],

                    'limitations' =>
                        $fallback['limitations'],

                    'context_snapshot' =>
                        $context,

                    'response_payload' => [
                        'fallback' => true,

                        'exception_class' =>
                            $exception::class,

                        'provider_error' =>
                            $exception->getMessage(),
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
     * Build a deterministic response from Helmio's own analytics when the AI
     * provider is unavailable or returns an invalid response.
     *
     * This is intentionally conservative. It never invents portfolio facts and
     * only summarizes data already present in the context snapshot.
     *
     * @param array<string, mixed> $context
     * @return array{
     *     answer: string,
     *     confidence: string,
     *     citations: array<int, array<string, mixed>>,
     *     limitations: array<int, string>
     * }
     */
    private function buildFallbackResponse(
        string $question,
        array $context,
    ): array {
        $helmScore = is_array(
            $context['helm_score'] ?? null
        )
            ? $context['helm_score']
            : [];

        $categories = is_array(
            $helmScore['categories'] ?? null
        )
            ? $helmScore['categories']
            : [];

        $availability = is_array(
            $context['data_availability'] ?? null
        )
            ? $context['data_availability']
            : [];

        $findings = collect(
            $context['open_findings'] ?? [],
        )
            ->filter(
                fn (mixed $finding): bool =>
                    is_array($finding)
            )
            ->values();

        $overallScore =
            $helmScore['overall_score'] ?? null;

        $overallLabel =
            $helmScore['overall_label'] ?? null;

        $categoryLabels = [
            'cost' => 'costs',
            'diversification' => 'diversification',
            'performance' => 'performance',
            'risk' => 'risk',
            'trading' => 'trading activity',
            'cash' => 'cash allocation',
            'tax' => 'tax efficiency',
        ];

        $scoredCategories = collect(
            $categoryLabels,
        )
            ->map(
                function (
                    string $label,
                    string $key,
                ) use ($categories): ?array {
                    $category =
                        $categories[$key] ?? null;

                    if (! is_array($category)) {
                        return null;
                    }

                    $score = $category['score']
                        ?? null;

                    if (! is_numeric($score)) {
                        return null;
                    }

                    return [
                        'key' => $key,
                        'label' => $label,
                        'score' => (int) round(
                            (float) $score
                        ),
                        'category' => $category,
                    ];
                }
            )
            ->filter()
            ->sortBy('score')
            ->values();

        $concerns = $scoredCategories
            ->filter(
                fn (array $category): bool =>
                    $category['score'] < 70
            )
            ->take(3);

        $strengths = $scoredCategories
            ->filter(
                fn (array $category): bool =>
                    $category['score'] >= 80
            )
            ->sortByDesc('score')
            ->take(2);

        $parts = [];

        if (
            is_numeric($overallScore)
        ) {
            $scoreText =
                'Your current Helm Score is '
                .(int) round((float) $overallScore);

            if (
                is_string($overallLabel)
                && trim($overallLabel) !== ''
            ) {
                $scoreText .=
                    ' ('.$overallLabel.')';
            }

            $parts[] = $scoreText.'.';
        }

        if ($concerns->isNotEmpty()) {
            $concernText = $concerns
                ->map(
                    fn (array $category): string =>
                        ucfirst($category['label'])
                        .' is currently one of the weaker areas'
                        .' (score '.$category['score'].')'
                )
                ->implode('; ');

            $parts[] =
                'The areas that most deserve attention are: '
                .$concernText.'.';
        } elseif ($findings->isNotEmpty()) {
            $findingTitles = $findings
                ->take(3)
                ->pluck('title')
                ->filter()
                ->implode('; ');

            if ($findingTitles !== '') {
                $parts[] =
                    'Helmio currently has these items flagged for review: '
                    .$findingTitles.'.';
            }
        } elseif ($scoredCategories->isNotEmpty()) {
            $parts[] =
                'Helmio does not currently show a major weakness among the available scored categories.';
        }

        if ($strengths->isNotEmpty()) {
            $strengthText = $strengths
                ->map(
                    fn (array $category): string =>
                        $category['label']
                        .' ('.$category['score'].')'
                )
                ->implode(' and ');

            $parts[] =
                'Stronger areas include '
                .$strengthText.'.';
        }

        $unavailable = collect(
            $availability,
        )
            ->only([
                'cost',
                'diversification',
                'performance',
                'risk',
                'trading',
                'cash',
                'tax',
                'suitability',
            ])
            ->filter(
                fn (mixed $available): bool =>
                    $available === false
            )
            ->keys()
            ->values();

        if ($unavailable->isNotEmpty()) {
            $parts[] =
                'Some areas could not be evaluated completely: '
                .$unavailable->implode(', ')
                .'.';
        }

        if ($parts === []) {
            $parts[] =
                'Helmio has portfolio data available, but it does not currently contain enough scored analytics to summarize the main concerns reliably.';
        }

        $parts[] =
            'This response was generated from Helmio\'s stored analytics because the AI explanation service was temporarily unavailable.';

        $citations = $findings
            ->take(3)
            ->map(
                function (array $finding): ?array {
                    $id = $finding['id'] ?? null;

                    if (! is_numeric($id)) {
                        return null;
                    }

                    return [
                        'type' => 'audit_finding',
                        'id' => (int) $id,
                        'label' =>
                            (string) (
                                $finding['title']
                                ?? 'Advisor Audit finding'
                            ),
                        'route_name' =>
                            $finding['route_name']
                            ?? null,
                        'route_parameter' => null,
                    ];
                }
            )
            ->filter()
            ->values()
            ->all();

        $limitations = collect(
            $context['limitations'] ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value)
                    && trim($value) !== ''
            )
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $confidence = $scoredCategories->count() >= 4
            ? 'medium'
            : 'low';

        return [
            'answer' =>
                implode(' ', $parts),

            'confidence' =>
                $confidence,

            'citations' =>
                $citations,

            'limitations' =>
                $limitations,
        ];
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