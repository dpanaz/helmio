<?php

namespace Tests\Feature;

use App\Jobs\BuildPortfolioAnalytics;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Analytics\Pipeline\PortfolioAnalyticsPipelineService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class BuildPortfolioAnalyticsRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_http_429_still_releases_after_twenty_attempts(): void
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
            $queueJob->shouldReceive('attempts')->once()->andReturn(21);
            $queueJob->shouldReceive('release')->once()->with(75);

            $job = new BuildPortfolioAnalytics($run->id);
            $this->assertSame(120, $job->tries);

            $job->setJob($queueJob)->handle($pipeline);
        }

        $this->assertSame(PortfolioAnalysisRun::STATUS_SYNCING, $run->fresh()->status);
    }
}
