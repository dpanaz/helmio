<?php

namespace Tests\Feature;

use App\Jobs\BuildPortfolioAnalytics;
use App\Models\AiInsightRun;
use App\Models\AuditFinding;
use App\Models\HelmScoreSnapshot;
use App\Models\InvestorProfile;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Billing\SubscriptionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SimplifiedOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Queue::fake();
        $this->mock(SubscriptionAccessService::class, function ($mock): void {
            $mock->shouldReceive('hasPremiumAccess')->andReturn(true);
        });
    }

    private function customer(): User
    {
        return User::factory()->create();
    }

    private function profile(User $user): void
    {
        InvestorProfile::query()->create(['user_id' => $user->id, ...$this->answers()]);
    }

    private function answers(): array
    {
        return ['primary_objective' => 'growth', 'time_horizon_years' => 10,
            'investment_experience' => 'beginner', 'liquidity_needs' => 'low', 'risk_tolerance' => 'moderate'];
    }

    private function connected(User $user): void
    {
        $this->profile($user);
        $user->investmentAccounts()->create(['name' => 'Test account', 'account_type' => 'brokerage', 'currency' => 'USD', 'current_value' => 1000]);
    }

    private function ready(User $user, ?int $score = 47): PortfolioAnalysisRun
    {
        HelmScoreSnapshot::query()->create(['user_id' => $user->id, 'overall_score' => $score,
            'data_completeness' => 0.5, 'score_details' => ['overall_label' => 'Needs attention'],
            'formula_version' => 'test', 'calculated_for_date' => today()]);
        $run = PortfolioAnalysisRun::query()->create(['user_id' => $user->id, 'status' => 'ready', 'current_step' => 'complete', 'completed_at' => now()->subMinute()]);
        AiInsightRun::query()->create(['user_id' => $user->id, 'provider' => 'test', 'model' => 'test',
            'status' => 'completed', 'context_version' => 'test', 'prompt_version' => 'test',
            'context_snapshot' => [], 'summary' => 'Your calculated results explained.', 'generated_at' => now(), 'is_stale' => false]);
        return $run;
    }

    public function test_welcome_goes_directly_to_five_question_profile(): void
    {
        $this->actingAs($this->customer())->get(route('onboarding.welcome'))->assertRedirect(route('onboarding.profile'));
        $this->get(route('onboarding.profile'))->assertOk()->assertSee('Step 1 of 3')->assertSee('time_horizon_years');
    }

    public function test_profile_requires_all_five_valid_answers_and_saves_them(): void
    {
        $user = $this->customer();
        $this->actingAs($user)->post(route('onboarding.profile.save'), ['risk_tolerance' => 'invented'])
            ->assertSessionHasErrors(array_keys($this->answers()));
        $this->assertDatabaseCount('investor_profiles', 0);
        $this->post(route('onboarding.profile.save'), $this->answers())->assertRedirect(route('onboarding.connect'));
        $this->assertDatabaseHas('investor_profiles', ['user_id' => $user->id, ...$this->answers()]);
    }

    public function test_connected_customer_cannot_skip_preparation_or_first_review(): void
    {
        $user = $this->customer();
        $this->connected($user);
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('onboarding.syncing'));
        $this->post(route('onboarding.finish'))->assertRedirect(route('onboarding.syncing'));
        $this->assertNull($user->fresh()->onboarding_completed_at);
        $this->get(route('onboarding.status'))->assertJsonPath('ready', false);
    }

    public function test_legacy_reveal_urls_share_one_review_and_finish_unlocks_dashboard(): void
    {
        $user = $this->customer();
        $this->connected($user);
        $this->ready($user);
        $this->actingAs($user);
        foreach (['reveal', 'score', 'findings', 'executive-summary', 'complete'] as $route) {
            $this->get(route('onboarding.'.$route))->assertOk()->assertViewIs('onboarding.complete')->assertSee('Your calculated results explained.');
        }
        $this->post(route('onboarding.finish'))->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->onboarding_completed_at);
        $this->get(route('dashboard'))->assertOk()->assertSee('What needs my attention?');
        PortfolioAnalysisRun::query()->create(['user_id' => $user->id, 'status' => 'analyzing']);
        $this->get(route('dashboard'))->assertOk()->assertSee('Your review is updating.');
    }

    public function test_missing_score_is_not_zero_and_review_limits_findings_to_three(): void
    {
        $user = $this->customer();
        $this->connected($user);
        $this->ready($user, null);
        foreach (range(1, 4) as $index) {
            AuditFinding::query()->create(['user_id' => $user->id, 'fingerprint' => 'f'.$index,
                'category' => 'cost', 'title' => 'Finding '.$index, 'description' => 'Evidence '.$index,
                'first_detected_at' => now(), 'last_detected_at' => now(), 'severity' => $index === 1 ? 'critical' : 'low', 'status' => 'open']);
        }
        $other = $this->customer();
        AuditFinding::query()->create(['user_id' => $other->id, 'fingerprint' => 'other', 'category' => 'cost', 'title' => 'Private other finding', 'description' => 'Other evidence', 'first_detected_at' => now(), 'last_detected_at' => now(), 'severity' => 'critical', 'status' => 'open']);
        $this->actingAs($user)->get(route('onboarding.complete'))->assertOk()
            ->assertSee('Not enough data to assess')->assertSee('Finding 1')
            ->assertDontSee('Finding 2')->assertDontSee('Private other finding');
    }

    public function test_summary_waits_for_current_analytics_then_degrades_explicitly(): void
    {
        $user = $this->customer();
        $this->connected($user);
        $run = $this->ready($user);
        AiInsightRun::query()->where('user_id', $user->id)->update(['generated_at' => now()->subHour()]);
        $this->actingAs($user)->get(route('onboarding.status'))->assertJsonPath('ready', false);
        $run->update(['completed_at' => now()->subMinutes(6)]);
        $this->get(route('onboarding.status'))->assertJsonPath('ready', true)->assertJsonPath('summary_unavailable', true);
        $this->get(route('onboarding.complete'))->assertOk()->assertDontSee('Your calculated results explained.');
    }

    public function test_retry_claims_failed_analysis_once(): void
    {
        $user = $this->customer();
        $this->connected($user);
        $run = PortfolioAnalysisRun::query()->create(['user_id' => $user->id, 'status' => 'failed']);
        $this->actingAs($user)->get(route('onboarding.status'))->assertJsonPath('failed', true)->assertJsonPath('ready', false);
        $this->post(route('onboarding.retry'))->assertRedirect(route('onboarding.syncing'));
        Queue::assertPushed(BuildPortfolioAnalytics::class, fn ($job) => $job->analysisRunId === $run->id);
        $this->assertSame('pending', $run->fresh()->status);
    }
}
