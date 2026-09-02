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
    ): MarketingConversion {
        $visit ??= $this->resolveVisit();

        if ($visit && $user) {
            $visit->update([
                'user_id' => $user->id,
            ]);
        }

        $conversionId = implode('_', [
            'helmio',
            Str::lower($type),
            $user?->id
                ?? $visit?->visitor_uuid
                ?? Str::uuid(),
        ]);

        $conversion = MarketingConversion::query()
            ->firstOrCreate(
                [
                    'conversion_id' => $conversionId,
                ],
                [
                    'marketing_visit_id' => $visit?->id,
                    'user_id' => $user?->id,
                    'type' => $type,
                    'value' => $value,
                    'currency' => 'USD',
                    'converted_at' => now(),
                    'reddit_status' => 'pending',
                    'metadata' => $metadata,
                ],
            );

        if ($conversion->wasRecentlyCreated) {
            SendRedditConversion::dispatch(
                $conversion,
            );
        }

        return $conversion;
    }

    private function resolveVisit(): ?MarketingVisit
    {
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

        $visitorUuid = request()->cookie(
            'helmio_visitor_uuid',
        );

        if (! $visitorUuid) {
            return null;
        }

        return MarketingVisit::query()
            ->where(
                'visitor_uuid',
                $visitorUuid,
            )
            ->latest('first_seen_at')
            ->first();
    }
}