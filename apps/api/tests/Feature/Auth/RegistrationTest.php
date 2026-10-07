<?php

namespace Tests\Feature\Auth;

use App\Models\SupportConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.registration_open', true);
    }

    public function test_closed_registration_redirects_and_rejects_new_accounts(): void
    {
        config()->set('app.registration_open', false);
        $this->get('/register')->assertRedirect('/');
        $this->post('/register', [
            'name' => 'Test User', 'email' => 'test@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertForbidden();
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $conversation = SupportConversation::query()
            ->where('user_id', auth()->id())
            ->where('subject', 'Welcome to Helmio')
            ->firstOrFail();

        $this->assertSame(SupportConversation::STATUS_WAITING_CUSTOMER, $conversation->status);
        $this->assertSame(1, $conversation->messages()->count());
        $this->assertSame('staff', $conversation->messages()->first()->sender_type);
        $this->assertFalse($conversation->messages()->first()->is_internal);
    }
}
