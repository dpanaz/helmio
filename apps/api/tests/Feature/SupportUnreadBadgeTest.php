<?php

namespace Tests\Feature;

use App\Models\SupportConversation;
use App\Models\User;
use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportUnreadBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_message_shows_badge_until_customer_opens_conversation(): void
    {
        $this->withoutMiddleware(EnsureOnboardingComplete::class);
        $customer = User::factory()->create();
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
            'body' => 'Welcome!',
            'is_internal' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('support.index'))
            ->assertOk()
            ->assertSee('1 unread support conversations');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('You have a new message from Helmio Support.');

        $this->get(route('support.show', $conversation))->assertOk();

        $this->get(route('support.index'))
            ->assertOk()
            ->assertDontSee('1 unread support conversations');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('You have a new message from Helmio Support.');
    }
}
