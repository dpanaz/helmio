<?php

namespace App\Jobs;

use App\Models\MarketingConversion;
use App\Services\Marketing\RedditConversionsService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class SendRedditConversion implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 30;

    public int $uniqueFor = 3600;

    public array $backoff = [
        60,
        300,
        900,
    ];

    public function __construct(
        public MarketingConversion $conversion,
    ) {
        $this->onQueue('marketing');
    }

    public function uniqueId(): string
    {
        return $this->conversion->conversion_id;
    }

    public function handle(
        RedditConversionsService $reddit,
    ): void {
        if (
            ! config(
                'services.reddit.conversions_enabled',
            )
        ) {
            return;
        }

        if (
            $this->conversion->reddit_status === 'sent'
        ) {
            return;
        }

        $this->conversion->increment(
            'reddit_attempts',
        );

        $response = $reddit->send(
            $this->conversion,
        );

        if (! $response->successful()) {
            $this->conversion->update([
                'reddit_status' => 'failed',
                'reddit_error' => sprintf(
                    'HTTP %d: %s',
                    $response->status(),
                    $response->body(),
                ),
            ]);

            throw new RuntimeException(
                'Reddit CAPI returned HTTP ' .
                $response->status(),
            );
        }

        $this->conversion->update([
            'reddit_status' => 'sent',
            'reddit_sent_at' => now(),
            'reddit_error' => null,
        ]);
    }

    public function failed(
        ?Throwable $exception,
    ): void {
        $this->conversion->update([
            'reddit_status' => 'failed',
            'reddit_error' =>
                $exception?->getMessage()
                ?? 'Reddit conversion job failed.',
        ]);
    }
}