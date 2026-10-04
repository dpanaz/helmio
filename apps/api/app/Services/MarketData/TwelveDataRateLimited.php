<?php

namespace App\Services\MarketData;

use RuntimeException;
use Illuminate\Http\Client\Response;
use Throwable;

class TwelveDataRateLimited extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $retryAfterSeconds = 75,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $previous);
    }

    public static function retryDelayFor(Response $response): int
    {
        $message = strtolower((string) $response->json('message'));

        if (str_contains($message, 'for the day') || str_contains($message, 'daily')) {
            // Basic plan daily credits reset at midnight UTC. Allow one minute
            // for the provider's counters to settle before trying again.
            return max(75, now('UTC')->addDay()->startOfDay()->addMinute()->timestamp - now()->timestamp);
        }

        return 75;
    }
}
