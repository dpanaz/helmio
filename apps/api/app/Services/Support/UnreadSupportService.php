<?php

namespace App\Services\Support;

use App\Models\SupportConversation;

class UnreadSupportService
{
    public function countFor(int $userId): int
    {
        return SupportConversation::query()
            ->where('user_id', $userId)
            ->whereHas('messages', fn ($query) => $query
                ->where('sender_type', 'staff')
                ->where('is_internal', false)
                ->where(function ($query) {
                    $query->whereNull('support_conversations.customer_last_read_at')
                        ->orWhereColumn('support_messages.created_at', '>', 'support_conversations.customer_last_read_at');
                }))
            ->count();
    }
}
