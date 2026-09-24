<?php

namespace Tests\Unit;

use App\Support\SafeFailureMessage;
use PHPUnit\Framework\TestCase;

class SafeFailureMessageTest extends TestCase
{
    public function test_it_redacts_snaptrade_secrets_from_error_urls(): void
    {
        $message = 'GET https://api.snaptrade.com/accounts?userId=helmio-user-12&userSecret=private-value&clientId=HELMIO';
        $safe = SafeFailureMessage::redact($message);

        self::assertStringNotContainsString('private-value', $safe);
        self::assertStringContainsString('userSecret=[REDACTED]', $safe);
        self::assertStringContainsString('userId=helmio-user-12', $safe);
    }

    public function test_it_redacts_json_secret_values(): void
    {
        $safe = SafeFailureMessage::redact('{"userSecret":"private-value"}');

        self::assertStringNotContainsString('private-value', $safe);
        self::assertStringContainsString('[REDACTED]', $safe);
    }
}
