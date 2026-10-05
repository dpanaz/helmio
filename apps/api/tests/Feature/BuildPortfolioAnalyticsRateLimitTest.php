<?php

namespace Tests\Feature;

use App\Jobs\BuildPortfolioAnalytics;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Analytics\Pipeline\PortfolioAnalyticsPipelineService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class BuildPortfolioAnalyticsRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_http_429_releases_even_after_attempt_limit(): void
    {
        $user = User::factory()->create();
        $run = PortfolioAnalysisRun::query()->create(['user_id' => $user->id]);

        Http::fake(['*' => Http::response('Minute credits exhausted', 429)]);

        try {
            Http::get('https://example.test/rate-limit')->throw();
            $this->fail('Expected an HTTP 429 exception.');
        } catch (RequestException $exception) {
            $pipeline = Mockery::mock(PortfolioAnalyticsPipelineService::class);
            $pipeline->shouldReceive('run')->once()->andThrow($exception);

            $queueJob = Mockery::mock(QueueJob::class);
            $queueJob->shouldReceive('release')->once()->with(75);

            $job = new BuildPortfolioAnalytics($run->id);
            $this->assertSame(120, $job->tries);
            $this->assertSame(3, $job->maxExceptions);
            $this->assertGreaterThanOrEqual(
                now()->addDays(7)->subSecond()->timestamp,
                $job->retryUntil()->getTimestamp(),
            );

            $job->setJob($queueJob)->handle($pipeline);
        }

        $this->assertSame(PortfolioAnalysisRun::STATUS_SYNCING, $run->fresh()->status);
    }

    public function test_raw_daily_429_releases_until_after_midnight_utc(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-03 10:00:00', 'UTC'));
        $user = User::factory()->create();
        $run = PortfolioAnalysisRun::query()->create(['user_id' => $user->id]);
        Http::fake(['*' => Http::response([
            'message' => 'You have run out of API credits for the day.',
        ], 429)]);

        try {
            Http::get('https://example.test/daily-limit')->throw();
            $this->fail('Expected an HTTP 429 exception.');
        } catch (RequestException $exception) {
            $pipeline = Mockery::mock(PortfolioAnalyticsPipelineService::class);
            $pipeline->shouldReceive('run')->once()->andThrow($exception);

            $queueJob = Mockery::mock(QueueJob::class);
            $queueJob->shouldReceive('release')->once()->with(50460);

            (new BuildPortfolioAnalytics($run->id))
                ->setJob($queueJob)
                ->handle($pipeline);
        }

        $this->assertSame(PortfolioAnalysisRun::STATUS_SYNCING, $run->fresh()->status);
    }
}
