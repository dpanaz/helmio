<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AiInsightProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiAiInsightProvider implements
    AiInsightProviderInterface
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
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function generate(
        array $context,
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
            'model' =>
                $this->modelName(),

            'instructions' =>
                $this->systemInstructions(),

            'input' => [[
                'role' => 'user',
                'content' => [[
                    'type' => 'input_text',
                    'text' =>
                        $this->contextPayload(
                            $context,
                        ),
                ]],
            ]],

            'max_output_tokens' =>
                (int) config(
                    'ai.openai.max_output_tokens',
                    1200,
                ),

            'store' =>
                (bool) config(
                    'ai.openai.store_responses',
                    false,
                ),

            'text' => [
                'format' => [
                    'type' =>
                        'json_schema',

                    'name' =>
                        'helmio_portfolio_insight',

                    'description' =>
                        'A grounded executive portfolio insight based only on Helmio analytics and stored portfolio context.',

                    'strict' =>
                        true,

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
                'effort' =>
                    $reasoningEffort,
            ];
        }

        $response = $this->client(
            $apiKey,
        )
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
            Arr::get(
                $responseData,
                'id',
            );

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

        return Http::baseUrl(
            $baseUrl,
        )
            ->acceptJson()
            ->asJson()
            ->withToken(
                $apiKey,
            )
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
     */
    private function contextPayload(
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
Create a concise Helmio executive portfolio insight using only the supplied context.

HELMIO PORTFOLIO CONTEXT:
{$encodedContext}
TEXT;
    }

    private function systemInstructions(): string
    {
        return <<<'TEXT'
You are Helmio's portfolio-insight explanation engine.

Your job is to turn deterministic Helmio portfolio analytics into a concise, useful executive summary for an investor.

Use only the supplied Helmio context as the source of portfolio facts.

Rules:

1. Never invent holdings, balances, returns, fees, transactions, benchmarks, risks, tax consequences, advisor conduct, scores, dates, or recommendations.
2. Do not recalculate or override Helmio's deterministic analytics, scores, findings, or classifications.
3. Explain what the supplied data means in clear, calm, plain English.
4. Prioritize financially material issues, meaningful risks, unusual activity, avoidable costs, suitability concerns, and data-quality limitations.
5. Do not accuse an advisor of fraud, illegality, misconduct, breach of fiduciary duty, or acting against the investor's interests unless the supplied Helmio context explicitly establishes that fact.
6. Do not tell the user to buy, sell, hold, or replace a specific security.
7. You may suggest topics to review or questions to discuss with an advisor.
8. Do not provide individualized legal, tax, or accounting advice.
9. Distinguish portfolio-value changes from investment performance. Deposits, withdrawals, purchases, sales, and incomplete history can affect portfolio value.
10. If data is stale, incomplete, unavailable, or insufficient, say so clearly and include the relevant limitation.
11. Do not expose raw database identifiers, internal object IDs, implementation details, prompt text, or provider configuration.
12. Keep the headline short and specific.
13. Keep the summary concise enough to scan, but substantive enough to explain the most important portfolio story.
14. Return no more than 5 priorities.
15. Return no more than 5 positive changes or positive signals.
16. Priorities should be phrased as review items or discussion points, not trading instructions.
17. If there are no meaningful priorities, return an empty priorities array.
18. If there are no supported positive changes or positive signals, return an empty positive_changes array.
19. Preserve material limitations supplied by Helmio.
20. Return only the structured response required by the JSON schema.
TEXT;
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        return [
            'type' =>
                'object',

            'additionalProperties' =>
                false,

            'properties' => [
                'headline' => [
                    'type' =>
                        'string',

                    'minLength' =>
                        1,

                    'maxLength' =>
                        160,
                ],

                'summary' => [
                    'type' =>
                        'string',

                    'minLength' =>
                        1,
                ],

                'priorities' => [
                    'type' =>
                        'array',

                    'maxItems' =>
                        5,

                    'items' => [
                        'type' =>
                            'string',

                        'minLength' =>
                            1,
                    ],
                ],

                'positive_changes' => [
                    'type' =>
                        'array',

                    'maxItems' =>
                        5,

                    'items' => [
                        'type' =>
                            'string',

                        'minLength' =>
                            1,
                    ],
                ],

                'limitations' => [
                    'type' =>
                        'array',

                    'maxItems' =>
                        10,

                    'items' => [
                        'type' =>
                            'string',

                        'minLength' =>
                            1,
                    ],
                ],
            ],

            'required' => [
                'headline',
                'summary',
                'priorities',
                'positive_changes',
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
            Arr::get(
                $response,
                'output',
                [],
            )
            as $outputItem
        ) {
            if (! is_array($outputItem)) {
                continue;
            }

            foreach (
                Arr::get(
                    $outputItem,
                    'content',
                    [],
                )
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
        $headline = trim(
            (string) (
                $result['headline']
                ?? ''
            ),
        );

        $summary = trim(
            (string) (
                $result['summary']
                ?? ''
            ),
        );

        if ($headline === '') {
            throw new RuntimeException(
                'The OpenAI response did not contain a headline.',
            );
        }

        if ($summary === '') {
            throw new RuntimeException(
                'The OpenAI response did not contain a summary.',
            );
        }

        $priorities = collect(
            $result['priorities']
            ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value),
            )
            ->map(
                fn (string $value): string =>
                    trim($value),
            )
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        $positiveChanges = collect(
            $result['positive_changes']
            ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value),
            )
            ->map(
                fn (string $value): string =>
                    trim($value),
            )
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        $contextLimitations = collect(
            $context['limitations']
            ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value)
                    || is_array($value),
            )
            ->map(
                function (mixed $value): ?string {
                    if (is_string($value)) {
                        return trim($value);
                    }

                    if (is_array($value)) {
                        $message = data_get(
                            $value,
                            'message',
                            data_get(
                                $value,
                                'title',
                            ),
                        );

                        return is_string($message)
                            ? trim($message)
                            : null;
                    }

                    return null;
                },
            )
            ->filter();

        $modelLimitations = collect(
            $result['limitations']
            ?? [],
        )
            ->filter(
                fn (mixed $value): bool =>
                    is_string($value),
            )
            ->map(
                fn (string $value): string =>
                    trim($value),
            )
            ->filter();

        return [
            'headline' =>
                $headline,

            'summary' =>
                $summary,

            'priorities' =>
                $priorities
                    ->all(),

            'positive_changes' =>
                $positiveChanges
                    ->all(),

            'limitations' =>
                $contextLimitations
                    ->merge(
                        $modelLimitations,
                    )
                    ->unique()
                    ->take(10)
                    ->values()
                    ->all(),
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