<?php

namespace App\Services\Marketing;

use App\Jobs\SendRedditConversion;
use App\Models\MarketingConversion;
use App\Models\MarketingVisit;
use App\Models\User;
use Illuminate\Support\Str;

class MarketingConversionService
{
    public function record(
        string $type,
        ?User $user = null,
        ?MarketingVisit $visit = null,
        ?float $value = null,
        array $metadata = [],
        ?string $conversionId = null,
    ): MarketingConversion {
        $visit ??= $this->resolveVisit(
            $user,
        );

        if ($visit && $user) {
            $visit->update([
                'user_id' => $user->id,
            ]);
        }

        /*
         * Callers such as Stripe webhook listeners can
         * provide a stable external ID for deduplication.
         * Other conversions receive Helmio's default ID.
         */
        $conversionId ??= implode('_', [
            'helmio',
            Str::lower($type),
            $user?->id
                ?? $visit?->visitor_uuid
                ?? Str::uuid(),
        ]);

        $conversion = MarketingConversion::query()
            ->firstOrCreate(
                [
                    'conversion_id' =>
                        $conversionId,
                ],
                [
                    'marketing_visit_id' =>
                        $visit?->id,

                    'user_id' =>
                        $user?->id,

                    'type' =>
                        $type,

                    'value' =>
                        $value,

                    'currency' =>
                        'USD',

                    'converted_at' =>
                        now(),

                    'reddit_status' =>
                        'pending',

                    'metadata' =>
                        $metadata,
                ],
            );

        if ($conversion->wasRecentlyCreated) {
            SendRedditConversion::dispatch(
                $conversion,
            );
        }

        return $conversion;
    }

    private function resolveVisit(
        ?User $user = null,
    ): ?MarketingVisit {
        /*
         * Web requests can resolve attribution using
         * the visit ID stored in the session.
         */
        if (app()->bound('session')) {
            $visitId = session(
                'marketing_visit_id',
            );

            if ($visitId) {
                $visit = MarketingVisit::find(
                    $visitId,
                );

                if ($visit) {
                    return $visit;
                }
            }
        }

        /*
         * The visitor cookie provides a fallback when
         * the session has changed but the browser is
         * still the same.
         */
        if (app()->bound('request')) {
            $visitorUuid = request()->cookie(
                'helmio_visitor_uuid',
            );

            if ($visitorUuid) {
                $visit = MarketingVisit::query()
                    ->where(
                        'visitor_uuid',
                        $visitorUuid,
                    )
                    ->latest('first_seen_at')
                    ->first();

                if ($visit) {
                    return $visit;
                }
            }
        }

        /*
         * Webhooks and background jobs have no browser
         * session or cookie. Resolve attribution through
         * the user connected to the visit at signup.
         */
        if ($user) {
            return MarketingVisit::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->latest('first_seen_at')
                ->first();
        }

        return null;
    }
}