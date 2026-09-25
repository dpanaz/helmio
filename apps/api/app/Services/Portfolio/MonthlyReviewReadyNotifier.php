<?php

namespace App\Services\Portfolio;

use App\Models\MonthlyPortfolioReview;
use App\Models\User;
use App\Notifications\MonthlyPortfolioReviewReadyNotification;
use Illuminate\Notifications\DatabaseNotification;

class MonthlyReviewReadyNotifier
{
    public function notifyOnce(User $user, MonthlyPortfolioReview $review): void
    {
        if ($review->status !== MonthlyPortfolioReview::STATUS_COMPLETED) {
            return;
        }

        $eventKey = sprintf(
            'monthly-review-ready:%d:%s',
            $review->id,
            $review->period_start->format('Y-m'),
        );

        if (DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->where('data->event_key', $eventKey)
            ->exists()) {
            return;
        }

        $user->notify(new MonthlyPortfolioReviewReadyNotification($review));
    }
}
