<?php

namespace App\Services\Support;

use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WelcomeConversationService
{
    public function createFor(User $customer): SupportConversation
    {
        return DB::transaction(function () use ($customer): SupportConversation {
            $conversation = SupportConversation::query()->create([
                'user_id' => $customer->id,
                'channel' => SupportConversation::CHANNEL_TICKET,
                'status' => SupportConversation::STATUS_WAITING_CUSTOMER,
                'priority' => 'normal',
                'subject' => 'Welcome to Helmio',
                'last_message_at' => now(),
            ]);

            $conversation->messages()->create([
                'sender_type' => 'staff',
                'sender_user_id' => null,
                'body' => 'Thank you for trusting Helmio. If you have any questions or need help, send us a message here. We’re happy to help.',
                'is_internal' => false,
            ]);

            return $conversation;
        });
    }
}
