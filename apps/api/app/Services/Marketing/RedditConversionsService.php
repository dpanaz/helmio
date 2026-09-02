<?php

namespace App\Services\Marketing;

use App\Models\MarketingConversion;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class RedditConversionsService
{
    public function send(
        MarketingConversion $conversion,
    ): Response {
        $conversion->loadMissing([
            'visit',
            'user',
        ]);

        $visit = $conversion->visit;
        $user = $conversion->user;

        $trackingType = $this->trackingType(
            $conversion->type,
        );

        $event = [
            'event_at' => $conversion
                ->converted_at
                ->getTimestampMs(),

            'action_source' => 'WEBSITE',

            'type' => [
                'tracking_type' => $trackingType,
            ],

            'user' => array_filter(
                [
                    'ip_address' =>
                        $visit?->ip_address,

                    'user_agent' =>
                        $visit?->user_agent,

                    'email' =>
                        $user?->email,

                    'external_id' =>
                        $user
                            ? (string) $user->id
                            : null,

                    'uuid' =>
                        $visit?->visitor_uuid,
                ],
                fn ($value): bool =>
                    $value !== null
                    && $value !== '',
            ),

            'metadata' => array_filter(
                [
                    'conversion_id' =>
                        $conversion->conversion_id,

                    'currency' =>
                        $conversion->value !== null
                            ? $conversion->currency
                            : null,

                    'value' =>
                        $conversion->value !== null
                            ? (float) $conversion->value
                            : null,
                ],
                fn ($value): bool =>
                    $value !== null,
            ),
        ];

        if ($visit?->landing_page) {
            $event['event_source_url'] =
                $visit->landing_page;
        }

        if ($visit?->reddit_click_id) {
            $event['click_id'] =
                $visit->reddit_click_id;
        }

        if ($trackingType === 'CUSTOM') {
            $event['type']['custom_event_name'] =
                $conversion->type;
        }

        /*
         * Reddit CAPI v3 requires events and test_id
         * to be nested inside a top-level data object.
         */
        $payloadData = [
            'events' => [
                $event,
            ],
        ];

        /*
         * When REDDIT_TEST_ID is present, Reddit sends
         * the event to the Event Testing panel.
         */
        if (
            $testId = config(
                'services.reddit.test_id',
            )
        ) {
            $payloadData['test_id'] = $testId;
        }

        $payload = [
            'data' => $payloadData,
        ];

        return Http::withToken(
            config(
                'services.reddit.conversion_token',
            ),
        )
            ->acceptJson()
            ->asJson()
            ->withUserAgent(
                'web:myhelmio:v1.0 (by Helmio)',
            )
            ->timeout(15)
            ->post(
                sprintf(
                    'https://ads-api.reddit.com/api/v3/pixels/%s/conversion_events',
                    config(
                        'services.reddit.pixel_id',
                    ),
                ),
                $payload,
            );
    }

    private function trackingType(
        string $type,
    ): string {
        return match ($type) {
            'SIGN_UP' => 'SIGN_UP',
            'PURCHASE' => 'PURCHASE',
            'LEAD' => 'LEAD',
            'PAGE_VISIT' => 'PAGE_VISIT',
            default => 'CUSTOM',
        };
    }
}