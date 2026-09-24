<?php

namespace App\Support;

class SafeFailureMessage
{
    public static function redact(?string $message): string
    {
        return preg_replace(
            [
                '/((?:userSecret|user_secret|consumerKey|consumer_key)=)[^&\s]+/i',
                '/("(?:userSecret|user_secret|consumerKey|consumer_key)"\s*:\s*")[^"]+/i',
            ],
            '$1[REDACTED]',
            $message ?? '',
        ) ?? '';
    }
}
