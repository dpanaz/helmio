<?php

namespace App\Notifications;

use App\Models\MonthlyPortfolioReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MonthlyPortfolioReviewReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly MonthlyPortfolioReview $review,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event_key' => sprintf(
                'monthly-review-ready:%d:%s',
                $this->review->id,
                $this->review->period_start->format('Y-m'),
            ),

            'type' => 'monthly_review_ready',
            'severity' => $this->review->attention_event_count > 0
                ? 'medium'
                : 'positive',

            'category' => 'portfolio_review',

            'title' => sprintf(
                '%s portfolio review is ready',
                $this->review->period_start->format('F Y'),
            ),

            'message' => $this->review->summary
                ?: 'Your monthly portfolio review has been generated.',

            'action_label' => 'Open monthly review',

            'action_url' => route(
                'monthly-reviews.show',
                $this->review,
            ),

            'monthly_portfolio_review_id' =>
                $this->review->id,

            'created_for_date' =>
                now()->toDateString(),
        ];
    }
}