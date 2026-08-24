<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\PortfolioChatProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiPortfolioChatProvider implements
    PortfolioChatProviderInterface
{
    public function providerName(): string
    {
        return 'openai';
    }

    public function modelName(): ?string
    {
        return (string) config(
            'ai.openai.model',
            'gpt-5',
        );
    }

    /**
     * @param array<string, mixed> $context
     * @param array<int, array<string, string>> $history
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function answer(
        string $question,
        array $context,
        array $history = [],
    ): array {
        $apiKey = trim(
            (string) config(
                'ai.openai.api_key',
            ),
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'The OpenAI API key is not configured.',
            );
        }

        $payload = [
            'model' => $this->modelName(),

            'instructions' =>
                $this->systemInstructions(),

            'input' => $this->buildInput(
                question: $question,
                context: $context,
                history: $history,
            ),

            'max_output_tokens' => (int) config(
                'ai.openai.max_output_tokens',
                1200,
            ),

            'store' => (bool) config(
                'ai.openai.store_responses',
                false,
            ),

            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' =>
                        'ask_helmio_answer',
                    'description' =>
                        'A grounded portfolio explanation with internal Helmio citations.',
                    'strict' => true,
                    'schema' =>
                        $this->responseSchema(),
                ],
            ],
        ];

        $reasoningEffort = config(
            'ai.openai.reasoning_effort',
        );

        if (
            is_string($reasoningEffort)
            && $reasoningEffort !== ''
        ) {
            $payload['reasoning'] = [
                'effort' => $reasoningEffort,
            ];
        }

        $response = $this->client($apiKey)
            ->post(
                '/responses',
                $payload,
            )
            ->throw();

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new RuntimeException(
                'OpenAI returned an invalid response.',
            );
        }

        $outputText = $this->extractOutputText(
            $responseData,
        );

        $decoded = json_decode(
            $outputText,
            true,
        );

        if (! is_array($decoded)) {
            throw new RuntimeException(
                'OpenAI returned invalid structured JSON.',
            );
        }

        $result = $this->validateResult(
            $decoded,
            $context,
        );

        $result['provider_response_id'] =
            Arr::get($responseData, 'id');

        $result['input_tokens'] =
            $this->nullableInteger(
                Arr::get(
                    $responseData,
                    'usage.input_tokens',
                ),
            );

        $result['output_tokens'] =
            $this->nullableInteger(
                Arr::get(
                    $responseData,
                    'usage.output_tokens',
                ),
            );

        $result['total_tokens'] =
            $this->nullableInteger(
                Arr::get(
                    $responseData,
                    'usage.total_tokens',
                ),
            );

        return $result;
    }

    private function client(
        string $apiKey,
    ): PendingRequest {
        $baseUrl = rtrim(
            (string) config(
                'ai.openai.base_url',
                'https://api.openai.com/v1',
            ),
            '/',
        );

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->withHeaders([
                'X-Client-Request-Id' =>
                    (string) Str::uuid(),
            ])
            ->connectTimeout(
                (int) config(
                    'ai.openai.connect_timeout_seconds',
                    15,
                ),
            )
            ->timeout(
                (int) config(
                    'ai.openai.timeout_seconds',
                    90,
                ),
            )
            ->retry(
                times: 2,
                sleepMilliseconds: 750,
                throw: false,
            );
    }

    /**
     * @param array<string, mixed> $context
     * @param array<int, array<string, string>> $history
     * @return array<int, array<string, mixed>>
     */
    private function buildInput(
        string $question,
        array $context,
        array $history,
    ): array {
        $messages = collect($history)
            ->take(-10)
            ->filter(
                fn (array $message): bool =>
                    in_array(
                        $message['role'] ?? null,
                        ['user', 'assistant'],
                        true,
                    )
                    && filled(
                        $message['content'] ?? null,
                    ),
            )
            ->map(
                fn (array $message): array => [
                    'role' => $message['role'],
                    'content' => [[
                        'type' => 'input_text',
                        'text' => (string) $message[
                            'content'
                        ],
                    ]],
                ],
            )
            ->values();

        $messages->push([
            'role' => 'user',
            'content' => [[
                'type' => 'input_text',
                'text' => $this->questionPayload(
                    $question,
                    $context,
                ),
            ]],
        ]);

        return $messages->all();
    }

    /**
     * @param array<string, mixed> $context
     */
    private function questionPayload(
        string $question,
        array $context,
    ): string {
        $encodedContext = json_encode(
            $context,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR,
        );

        return <<<TEXT
Answer the user's question using the supplied Helmio portfolio context.

IMPORTANT:
- Review data_availability before deciding whether the question can be answered.
- Use all relevant analytics categories, not just one.
- For broad portfolio questions, synthesize the major findings across categories.
- Missing portfolio_snapshot alone is not a reason for low confidence.
- Missing one analytics category alone is not a reason for low confidence.
- If enough Helmio analytics exist to materially answer the question, provide the answer.
- Prioritize the most significant findings rather than listing every available metric.

USER QUESTION:
{$question}

HELMIO CONTEXT:
{$encodedContext}
TEXT;
    }

    private function systemInstructions(): string
    {
        return <<<'TEXT'
You are Ask Helmio, an investment-oversight and portfolio-explanation assistant.

Your job is to help the user understand their portfolio using the supplied Helmio data.

The supplied context may contain:
- Helm Score and category scores
- cost analytics
- diversification analytics
- performance analytics
- risk analytics
- trading analytics
- cash allocation analytics
- tax analytics
- suitability analytics
- Advisor Audit results
- audit findings
- holdings
- recent transactions
- portfolio timeline events
- monthly reviews
- AI portfolio insights
- investor profile information
- data freshness and availability

CORE BEHAVIOR

1. Treat the supplied Helmio context as the only source of facts about this user's portfolio.

2. You MAY synthesize information across multiple Helmio analytics categories.

3. Broad questions should receive broad portfolio analysis.

Examples of broad questions include:
- What is wrong with my portfolio?
- How am I doing?
- What should I worry about?
- What needs attention?
- Is my portfolio healthy?
- What are my biggest problems?
- How is my advisor doing?
- What should I ask my advisor?
- What could be improved?

For these questions, review all available analytics categories and identify the most important findings.

4. Prioritize findings by significance. Do not simply repeat every metric.

5. When answering a broad portfolio-health question, structure the explanation naturally around:
- the most important concerns,
- why they matter,
- the Helmio data supporting them,
- positive areas or strengths,
- and anything the user may want to investigate further.

6. Missing one analytics category does NOT mean the question cannot be answered.

If some categories are unavailable:
- answer using the available categories,
- mention the missing category only if it materially limits the answer,
- do not lower confidence solely because one nonessential category is unavailable.

7. Use data_availability to understand which analytics categories can be relied upon.

8. Use limitations only as context. A listed limitation is not automatically a reason to refuse or fail the answer.

9. Confidence should describe how well the AVAILABLE Helmio data supports the answer:

HIGH:
The available Helmio data directly supports the important conclusions, even if a nonessential category is unavailable.

MEDIUM:
The question can be substantially answered, but an important category or important historical data is unavailable.

LOW:
The available Helmio context does not contain enough information to materially answer the user's actual question.

Do NOT choose LOW merely because:
- portfolio_snapshot is unavailable,
- one analytics category is unavailable,
- some brokerage data is stale,
- or the context contains limitations.

10. If Helmio has enough information to identify meaningful strengths, weaknesses, risks, costs, performance issues, diversification issues, trading issues, cash issues, tax issues, or suitability issues, provide a useful answer.

11. Never invent holdings, prices, performance, fees, transactions, scores, dates, tax consequences, or adviser conduct.

12. Never claim that an adviser acted illegally, improperly, fraudulently, or against the user's interests unless the supplied context explicitly establishes that fact.

13. Explain calculations already supplied by Helmio. Do not replace, recalculate, or override Helmio's deterministic scores.

14. You may explain risks, tradeoffs, patterns, concerns, and items worth reviewing.

15. Do not instruct the user to buy, sell, hold, or replace a specific security.

16. Do not provide individualized legal, tax, or accounting advice.

17. Distinguish portfolio-value changes from investment performance. Portfolio value can change because of deposits, withdrawals, purchases, sales, market movement, or incomplete data.

18. Do not treat normal market volatility alone as evidence of adviser misconduct.

19. Keep answers clear, conversational, and useful. Prefer specific Helmio findings over generic investing advice.

20. For questions such as "What is wrong with my portfolio?", do not answer with a generic disclaimer. Identify the strongest supported concerns and strengths from the supplied analytics.

21. Citations should reference supporting Helmio records present in the supplied context when applicable.

22. Do not invent citations. If no valid persisted Helmio record supports a statement, omit the citation rather than creating one.

23. Never include raw database identifiers in the prose answer.

24. Return only the structured response required by the JSON schema.
TEXT;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        return [
            'type' => 'object',

            'additionalProperties' => false,

            'properties' => [
                'answer' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],

                'confidence' => [
                    'type' => 'string',
                    'enum' => [
                        'high',
                        'medium',
                        'low',
                    ],
                ],

                'citations' => [
                    'type' => 'array',

                    'items' => [
                        'type' => 'object',

                        'additionalProperties' =>
                            false,

                        'properties' => [
                            'type' => [
                                'type' => 'string',

                                'enum' => [
                                    'monthly_portfolio_review',
                                    'audit_run',
                                    'audit_finding',
                                    'timeline_event',
                                    'portfolio_state_snapshot',
                                    'ai_insight_run',
                                ],
                            ],

                            'id' => [
                                'type' => 'integer',
                            ],

                            'label' => [
                                'type' => 'string',
                            ],

                            'route_name' => [
                                'type' => [
                                    'string',
                                    'null',
                                ],
                            ],

                            'route_parameter' => [
                                'type' => [
                                    'integer',
                                    'null',
                                ],
                            ],
                        ],

                        'required' => [
                            'type',
                            'id',
                            'label',
                            'route_name',
                            'route_parameter',
                        ],
                    ],
                ],

                'limitations' => [
                    'type' => 'array',

                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],

            'required' => [
                'answer',
                'confidence',
                'citations',
                'limitations',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $response
     */
    private function extractOutputText(
        array $response,
    ): string {
        foreach (
            Arr::get($response, 'output', [])
            as $outputItem
        ) {
            if (! is_array($outputItem)) {
                continue;
            }

            foreach (
                Arr::get($outputItem, 'content', [])
                as $contentItem
            ) {
                if (! is_array($contentItem)) {
                    continue;
                }

                if (
                    ($contentItem['type'] ?? null)
                    === 'output_text'
                    && is_string(
                        $contentItem['text'] ?? null,
                    )
                ) {
                    return $contentItem['text'];
                }

                if (
                    ($contentItem['type'] ?? null)
                    === 'refusal'
                ) {
                    throw new RuntimeException(
                        (string) (
                            $contentItem['refusal']
                            ?? 'The model refused the request.'
                        ),
                    );
                }
            }
        }

        $status = Arr::get(
            $response,
            'status',
            'unknown',
        );

        $incompleteReason = Arr::get(
            $response,
            'incomplete_details.reason',
        );

        throw new RuntimeException(
            sprintf(
                'OpenAI returned no output text. Status: %s%s',
                $status,
                $incompleteReason
                    ? '; reason: '.$incompleteReason
                    : '',
            ),
        );
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function validateResult(
        array $result,
        array $context,
    ): array {
        $answer = trim(
            (string) ($result['answer'] ?? ''),
        );

        if ($answer === '') {
            throw new RuntimeException(
                'The OpenAI response did not contain an answer.',
            );
        }

        $confidence = $result['confidence']
            ?? 'low';

        if (
            ! in_array(
                $confidence,
                ['high', 'medium', 'low'],
                true,
            )
        ) {
            $confidence = 'low';
        }

        $citations = collect(
            $result['citations'] ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_array($value),
            )
            ->map(
                fn (array $citation): ?array =>
                    $this->validateCitation(
                        $citation,
                        $context,
                    ),
            )
            ->filter()
            ->unique(
                fn (array $citation): string =>
                    $citation['type']
                    .':'
                    .$citation['id'],
            )
            ->take(8)
            ->values()
            ->all();

        $contextLimitations = collect(
            $context['limitations'] ?? [],
        );

        $modelLimitations = collect(
            $result['limitations'] ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value),
            );

        return [
            'answer' => $answer,

            'confidence' => $confidence,

            'citations' => $citations,

            'limitations' =>
                $contextLimitations
                    ->merge($modelLimitations)
                    ->filter()
                    ->unique()
                    ->take(10)
                    ->values()
                    ->all(),
        ];
    }

    /**
     * @param array<string, mixed> $citation
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    private function validateCitation(
        array $citation,
        array $context,
    ): ?array {
        $type = $citation['type'] ?? null;
        $id = $this->nullableInteger(
            $citation['id'] ?? null,
        );

        if (! is_string($type) || $id === null) {
            return null;
        }

        $record = $this->findContextRecord(
            $type,
            $id,
            $context,
        );

        if ($record === null) {
            return null;
        }

        return [
            'type' => $type,
            'id' => $id,

            'label' => trim(
                (string) (
                    $citation['label']
                    ?? $record['label']
                ),
            ),

            'route_name' =>
                $record['route_name'],

            'route_parameter' =>
                $record['route_parameter'],
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    private function findContextRecord(
        string $type,
        int $id,
        array $context,
    ): ?array {
        return match ($type) {
            'monthly_portfolio_review' =>
                $this->singleContextRecord(
                    data_get(
                        $context,
                        'latest_monthly_review',
                    ),
                    $id,
                    'Monthly Portfolio Review',
                    'monthly-reviews.show',
                ),

            'audit_run' =>
                $this->singleContextRecord(
                    data_get(
                        $context,
                        'latest_audit',
                    ),
                    $id,
                    'Advisor Audit',
                    'advisor-audit.history.show',
                ),

            'portfolio_state_snapshot' =>
                $this->singleContextRecord(
                    data_get(
                        $context,
                        'portfolio_snapshot',
                    ),
                    $id,
                    'Portfolio Snapshot',
                    'portfolio-timeline.index',
                    false,
                ),

            'ai_insight_run' =>
                $this->singleContextRecord(
                    data_get(
                        $context,
                        'latest_ai_insight',
                    ),
                    $id,
                    'AI Portfolio Insight',
                    'ai-insights.show',
                ),

            'audit_finding' =>
                $this->collectionContextRecord(
                    data_get(
                        $context,
                        'open_findings',
                        [],
                    ),
                    $id,
                    'title',
                    null,
                ),

            'timeline_event' =>
                $this->collectionContextRecord(
                    data_get(
                        $context,
                        'recent_timeline_events',
                        [],
                    ),
                    $id,
                    'headline',
                    'portfolio-timeline.index',
                ),

            default => null,
        };
    }

    /**
     * @param array<string, mixed>|null $record
     * @return array<string, mixed>|null
     */
    private function singleContextRecord(
        ?array $record,
        int $id,
        string $label,
        ?string $routeName,
        bool $parameterized = true,
    ): ?array {
        if (
            $record === null
            || (int) ($record['id'] ?? 0) !== $id
        ) {
            return null;
        }

        return [
            'label' => $label,
            'route_name' => $routeName,
            'route_parameter' =>
                $parameterized ? $id : null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<string, mixed>|null
     */
    private function collectionContextRecord(
        array $records,
        int $id,
        string $labelKey,
        ?string $defaultRouteName,
    ): ?array {
        $record = collect($records)->first(
            fn (array $record): bool =>
                (int) ($record['id'] ?? 0) === $id,
        );

        if ($record === null) {
            return null;
        }

        return [
            'label' =>
                $record[$labelKey]
                ?? 'Supporting Helmio record',

            'route_name' =>
                $record['route_name']
                ?? $defaultRouteName,

            'route_parameter' =>
                null,
        ];
    }

    private function nullableInteger(
        mixed $value,
    ): ?int {
        if (
            $value === null
            || $value === ''
            || ! is_numeric($value)
        ) {
            return null;
        }

        return (int) $value;
    }
}