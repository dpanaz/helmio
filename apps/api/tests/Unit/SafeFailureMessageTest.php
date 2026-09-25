<?php

namespace Tests\Unit;

use App\Support\SafeFailureMessage;
use PHPUnit\Framework\TestCase;

class SafeFailureMessageTest extends TestCase
{
    public function test_it_finds_the_query_after_a_pdo_exception_and_hides_quoted_values(): void
    {
        $exception = "PDOException: Out of sort memory in Connection.php:440\n"
            . "Stack trace:\n#0 ...\n"
            . "Next Illuminate\\Database\\QueryException: Out of sort memory "
            . "(Connection: mysql, SQL: select * from `audit_runs` where `email` = 'private@example.com' order by `id` desc)\n";

        $sql = SafeFailureMessage::sqlFromException($exception);

        self::assertStringContainsString('select * from `audit_runs`', $sql);
        self::assertStringNotContainsString('private@example.com', $sql);
        self::assertNull(SafeFailureMessage::sqlFromException('PDOException: no SQL recorded'));
    }

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
