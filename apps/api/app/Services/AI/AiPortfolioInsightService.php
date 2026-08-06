<?php

namespace App\Services\AI;

use App\Contracts\AI\AiInsightProviderInterface;
use App\Models\AiInsightRun;
use App\Models\User;
use Throwable;

class AiPortfolioInsightService
{
    public const PROMPT_VERSION =
        'portfolio-insight-0.1.0';

    public function __construct(
        private readonly AiPortfolioContextService $contextService,
        private readonly AiInsightProviderInterface $provider,
    ) {
    }

    public function latestOrGenerate(
    User $user,
): AiInsightRun {
    $latest = AiInsightRun::query()
        ->where(
            'user_id',
            $user->id,
        )
        ->latest('generated_at')
        ->first();

    if (
        $latest !== null &&
        $latest->status === AiInsightRun::STATUS_COMPLETED &&
        ! $latest->is_stale
    ) {
        return $latest;
    }

    return $this->generate($user);
}

    public function generate(
        User $user,
    ): AiInsightRun {
        $context = $this->contextService->build(
            $user,
        );

        $limitations = collect(
            $context['limitations'] ?? [],
        );

        $portfolioValue = (float) data_get(
            $context,
            'portfolio.total_value',
            0,
        );

        $accountCount = (int) data_get(
            $context,
            'portfolio.account_count',
            0,
        );

        $portfolioLastUpdatedAt = $user
            ->investmentAccounts()
            ->max('provider_synced_at');

        if ($accountCount === 0) {
            return AiInsightRun::query()->create([
                'user_id' =>
                    $user->id,

                'provider' =>
                    $this->provider->providerName(),

                'model' =>
                    $this->provider->modelName(),

                'status' =>
                    AiInsightRun::STATUS_BLOCKED,

                'is_stale' =>
                    false,

                'stale_at' =>
                    null,

                'stale_reason' =>
                    null,

                'portfolio_value_at_generation' =>
                    $portfolioValue,

                'account_count_at_generation' =>
                    $accountCount,

                'portfolio_last_updated_at' =>
                    $portfolioLastUpdatedAt,

                'context_version' =>
                    AiPortfolioContextService::CONTEXT_VERSION,

                'prompt_version' =>
                    self::PROMPT_VERSION,

                'headline' =>
                    'More portfolio data is required',

                'summary' =>
                    'Connect or add an investment account before generating portfolio insights.',

                'priorities' =>
                    [],

                'positive_changes' =>
                    [],

                'limitations' =>
                    $limitations
                        ->values()
                        ->all(),

                'context_snapshot' =>
                    $context,

                'response_payload' =>
                    null,

                'generated_at' =>
                    now(),

                'error_message' =>
                    null,
            ]);
        }

        try {
            $response = $this->provider->generate(
                $context,
            );

            return AiInsightRun::query()->create([
                'user_id' =>
                    $user->id,

                'provider' =>
                    $this->provider->providerName(),

                'model' =>
                    $this->provider->modelName(),

                'status' =>
                    AiInsightRun::STATUS_COMPLETED,

                'is_stale' =>
                    false,

                'stale_at' =>
                    null,

                'stale_reason' =>
                    null,

                'portfolio_value_at_generation' =>
                    $portfolioValue,

                'account_count_at_generation' =>
                    $accountCount,

                'portfolio_last_updated_at' =>
                    $portfolioLastUpdatedAt,

                'context_version' =>
                    AiPortfolioContextService::CONTEXT_VERSION,

                'prompt_version' =>
                    self::PROMPT_VERSION,

                'headline' =>
                    $response['headline']
                    ?? null,

                'summary' =>
                    $response['summary']
                    ?? null,

                'priorities' =>
                    $response['priorities']
                    ?? [],

                'positive_changes' =>
                    $response['positive_changes']
                    ?? [],

                'limitations' =>
                    $response['limitations']
                    ?? [],

                'context_snapshot' =>
                    $context,

                'response_payload' =>
                    $response,

                'generated_at' =>
                    now(),

                'error_message' =>
                    null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return AiInsightRun::query()->create([
                'user_id' =>
                    $user->id,

                'provider' =>
                    $this->provider->providerName(),

                'model' =>
                    $this->provider->modelName(),

                'status' =>
                    AiInsightRun::STATUS_FAILED,

                'is_stale' =>
                    false,

                'stale_at' =>
                    null,

                'stale_reason' =>
                    null,

                'portfolio_value_at_generation' =>
                    $portfolioValue,

                'account_count_at_generation' =>
                    $accountCount,

                'portfolio_last_updated_at' =>
                    $portfolioLastUpdatedAt,

                'context_version' =>
                    AiPortfolioContextService::CONTEXT_VERSION,

                'prompt_version' =>
                    self::PROMPT_VERSION,

                'headline' =>
                    'Insight generation failed',

                'summary' =>
                    'Helmio could not generate the portfolio insight.',

                'priorities' =>
                    [],

                'positive_changes' =>
                    [],

                'limitations' =>
                    $limitations
                        ->values()
                        ->all(),

                'context_snapshot' =>
                    $context,

                'response_payload' =>
                    null,

                'generated_at' =>
                    now(),

                'error_message' =>
                    $exception->getMessage(),
            ]);
        }
    }
}