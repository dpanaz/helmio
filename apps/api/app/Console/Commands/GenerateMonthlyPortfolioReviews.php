<?php

namespace App\Console\Commands;

use App\Models\MonthlyPortfolioReview;
use App\Models\User;
use App\Notifications\MonthlyPortfolioReviewReadyNotification;
use App\Services\Portfolio\MonthlyPortfolioReviewService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Throwable;

class GenerateMonthlyPortfolioReviews extends Command
{
    protected $signature = 'helmio:generate-monthly-reviews
        {--user= : Generate a review for one user ID}
        {--month= : Review month in YYYY-MM format}
        {--force : Regenerate an existing monthly review}';

    protected $description =
        'Generate saved monthly portfolio reviews for eligible users';

    public function handle(
        MonthlyPortfolioReviewService $reviewService,
    ): int {
        $month = $this->resolveMonth();

        if ($month === null) {
            return self::FAILURE;
        }

        $query = User::query()
            ->whereHas('investmentAccounts');

        if ($this->option('user')) {
            $query->where(
                'id',
                (int) $this->option('user'),
            );
        }

        $generated = 0;
        $skipped = 0;
        $failed = 0;

        $query
            ->orderBy('id')
            ->chunkById(
                100,
                function ($users) use (
                    $reviewService,
                    $month,
                    &$generated,
                    &$skipped,
                    &$failed,
                ): void {
                    foreach ($users as $user) {
                        try {
                            $existing =
                                MonthlyPortfolioReview::query()
                                    ->where(
                                        'user_id',
                                        $user->id,
                                    )
                                    ->whereDate(
                                        'period_start',
                                        $month
                                            ->startOfMonth()
                                            ->toDateString(),
                                    )
                                    ->whereDate(
                                        'period_end',
                                        $month
                                            ->endOfMonth()
                                            ->toDateString(),
                                    )
                                    ->first();

                            if (
                                $existing !== null
                                && ! $this->option('force')
                            ) {
                                $this->line(
                                    sprintf(
                                        'Skipped user %d: review already exists.',
                                        $user->id,
                                    ),
                                );

                                $skipped++;
                                continue;
                            }

                            $review = $reviewService->generate(
                                $user,
                                $month,
                            );

                            if (
                                $review->status
                                === MonthlyPortfolioReview::STATUS_BLOCKED
                            ) {
                                $this->line(
                                    sprintf(
                                        'Skipped user %d: insufficient monthly data.',
                                        $user->id,
                                    ),
                                );

                                $skipped++;
                                continue;
                            }

                            $this->notifyOnce(
                                $user,
                                $review,
                            );

                            $this->info(
                                sprintf(
                                    'Generated %s review for user %d.',
                                    $month->format('F Y'),
                                    $user->id,
                                ),
                            );

                            $generated++;
                        } catch (Throwable $exception) {
                            report($exception);

                            $this->error(
                                sprintf(
                                    'Failed user %d: %s',
                                    $user->id,
                                    $exception->getMessage(),
                                ),
                            );

                            $failed++;
                        }
                    }
                },
            );

        $this->newLine();

        $this->table(
            [
                'Month',
                'Generated',
                'Skipped',
                'Failed',
            ],
            [[
                $month->format('F Y'),
                $generated,
                $skipped,
                $failed,
            ]],
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function resolveMonth(): ?CarbonImmutable
    {
        $monthOption = $this->option('month');

        if (! $monthOption) {
            return CarbonImmutable::now()
                ->subMonthNoOverflow()
                ->startOfMonth();
        }

        try {
            return CarbonImmutable::createFromFormat(
                '!Y-m',
                (string) $monthOption,
            )->startOfMonth();
        } catch (Throwable) {
            $this->error(
                'The --month option must use YYYY-MM format.',
            );

            return null;
        }
    }

    private function notifyOnce(
        User $user,
        MonthlyPortfolioReview $review,
    ): void {
        $eventKey = sprintf(
            'monthly-review-ready:%d:%s',
            $review->id,
            $review->period_start->format('Y-m'),
        );

        $exists = DatabaseNotification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass(),
            )
            ->where(
                'notifiable_id',
                $user->getKey(),
            )
            ->where(
                'data->event_key',
                $eventKey,
            )
            ->exists();

        if ($exists) {
            return;
        }

        $user->notify(
            new MonthlyPortfolioReviewReadyNotification(
                $review,
            ),
        );
    }
}