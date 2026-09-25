<?php

namespace Tests\Feature;

use App\Models\MonthlyPortfolioReview;
use App\Models\User;
use App\Services\Portfolio\MonthlyReviewReadyNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyReviewReadyNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_review_alert_is_sent_once_and_blocked_review_is_not_announced(): void
    {
        $customer = User::factory()->create();
        $review = MonthlyPortfolioReview::query()->create([
            'user_id' => $customer->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'generated_at' => now(),
            'status' => MonthlyPortfolioReview::STATUS_BLOCKED,
            'headline' => 'Monthly review',
            'summary' => 'Summary',
        ]);

        $notifier = app(MonthlyReviewReadyNotifier::class);
        $notifier->notifyOnce($customer, $review);
        $this->assertSame(0, $customer->notifications()->count());

        $review->update(['status' => MonthlyPortfolioReview::STATUS_COMPLETED]);
        $notifier->notifyOnce($customer, $review);
        $notifier->notifyOnce($customer, $review);

        $this->assertSame(1, $customer->notifications()->count());
        $this->assertSame('monthly_review_ready', $customer->notifications()->first()->data['type']);
    }
}
