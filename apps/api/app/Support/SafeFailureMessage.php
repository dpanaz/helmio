<?php

namespace App\Support;

class SafeFailureMessage
{
    public static function sqlFromException(?string $exception): ?string
    {
        if (! preg_match('/\bSQL:\s*([^\r\n]+)/i', $exception ?? '', $matches)) {
            return null;
        }

        // QueryException can interpolate bindings, including private values.
        // Keep the query shape while removing quoted values and URL secrets.
        $sql = preg_replace("/'(?:''|[^'])*'/", "'?'", $matches[1]);

        return self::redact($sql);
    }

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
