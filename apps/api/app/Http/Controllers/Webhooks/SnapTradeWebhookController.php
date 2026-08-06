<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSnapTradeWebhook;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SnapTradeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
    ): JsonResponse {
        try {
            $payload = $request->json()->all();

            abort_unless(
                is_array($payload)
                    && $payload !== [],
                Response::HTTP_BAD_REQUEST,
                'Invalid JSON payload.'
            );

            $this->verifyClient(
                $payload
            );

            $this->verifyTimestamp(
                $payload
            );

            $this->verifySignature(
                $request,
                $payload
            );

            $webhookId =
                $this->requiredString(
                    $payload['webhookId']
                    ?? $payload['webookId']
                    ?? null,
                    'webhookId'
                );

            /*
             * SnapTrade retries undelivered webhooks. Cache::add returns
             * false when the webhook ID has already been processed.
             */
            $accepted = Cache::add(
                key:
                    'snaptrade:webhook:'
                    .$webhookId,

                value:
                    true,

                ttl:
                    now()->addDays(7),
            );

            if (! $accepted) {
                return response()->json([
                    'status' =>
                        'duplicate',
                ]);
            }

            ProcessSnapTradeWebhook::dispatch(
                $payload
            );

            return response()->json(
                [
                    'status' =>
                        'accepted',
                ],
                Response::HTTP_ACCEPTED
            );
        } catch (Throwable $exception) {
            /*
             * Remove the replay guard when validation or dispatch fails,
             * allowing SnapTrade's retry to be processed later.
             */
            if (
                isset($payload)
                && is_array($payload)
            ) {
                $webhookId =
                    $payload['webhookId']
                    ?? $payload['webookId']
                    ?? null;

                if (is_string($webhookId)) {
                    Cache::forget(
                        'snaptrade:webhook:'
                        .$webhookId
                    );
                }
            }

            report($exception);

            throw $exception;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function verifyClient(
        array $payload
    ): void {
        $expectedClientId = (string) config(
            'services.snaptrade.client_id'
        );

        $receivedClientId =
            $this->requiredString(
                $payload['clientId'] ?? null,
                'clientId'
            );

        abort_unless(
            $expectedClientId !== ''
                && hash_equals(
                    $expectedClientId,
                    $receivedClientId
                ),
            Response::HTTP_UNAUTHORIZED,
            'Invalid SnapTrade client ID.'
        );
    }

    /**
     * SnapTrade recommends rejecting webhook payloads older than five
     * minutes to reduce replay risk.
     *
     * @param array<string, mixed> $payload
     */
    private function verifyTimestamp(
        array $payload
    ): void {
        $timestamp =
            $this->requiredString(
                $payload[
                    'eventTimestamp'
                ] ?? null,
                'eventTimestamp'
            );

        $sentAt =
            CarbonImmutable::parse(
                $timestamp
            );

        $ageInSeconds =
            abs(
                now()
                    ->diffInSeconds(
                        $sentAt,
                        false
                    )
            );

        abort_if(
            $ageInSeconds > 300,
            Response::HTTP_UNAUTHORIZED,
            'The SnapTrade webhook is too old.'
        );
    }

    /**
     * SnapTrade signs a recursively key-sorted compact JSON payload
     * with HMAC-SHA256 using the consumer key and Base64-encodes it.
     *
     * @param array<string, mixed> $payload
     */
    private function verifySignature(
        Request $request,
        array $payload
    ): void {
        $receivedSignature =
            $request->header(
                'Signature'
            );

        abort_unless(
            is_string($receivedSignature)
                && $receivedSignature !== '',
            Response::HTTP_UNAUTHORIZED,
            'Missing SnapTrade signature.'
        );

        $consumerKey = (string) config(
            'services.snaptrade.consumer_key'
        );

        abort_unless(
            $consumerKey !== '',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'SnapTrade consumer key is not configured.'
        );

        $canonicalPayload =
            $this->sortRecursively(
                $payload
            );

        $json = json_encode(
            $canonicalPayload,
            JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
        );

        $expectedSignature =
            base64_encode(
                hash_hmac(
                    'sha256',
                    $json,
                    $consumerKey,
                    true
                )
            );

        abort_unless(
            hash_equals(
                $expectedSignature,
                $receivedSignature
            ),
            Response::HTTP_UNAUTHORIZED,
            'Invalid SnapTrade signature.'
        );
    }

    private function requiredString(
        mixed $value,
        string $field,
    ): string {
        abort_unless(
            is_string($value)
                && trim($value) !== '',
            Response::HTTP_BAD_REQUEST,
            "Missing {$field}."
        );

        return trim($value);
    }

    private function sortRecursively(
        mixed $value
    ): mixed {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(
                fn (mixed $item): mixed =>
                    $this->sortRecursively(
                        $item
                    ),
                $value
            );
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] =
                $this->sortRecursively(
                    $item
                );
        }

        return $value;
    }
}