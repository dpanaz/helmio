<?php

namespace App\Services\Audit;

use App\Models\AuditFinding;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditFindingSyncService
{
    /**
     * @param Collection<int, array<string, mixed>> $findings
     * @return Collection<int, AuditFinding>
     */
    public function sync(
        User $user,
        Collection $findings,
    ): Collection {
        return DB::transaction(
            function () use ($user, $findings): Collection {
                $detectedFingerprints = collect();
                $savedFindings = collect();

                foreach ($findings as $finding) {
                    $fingerprint = $this->fingerprint(
                        $finding,
                    );

                    $detectedFingerprints->push(
                        $fingerprint,
                    );

                    $record = AuditFinding::query()
                        ->where('user_id', $user->id)
                        ->where('fingerprint', $fingerprint)
                        ->first();

                    if ($record === null) {
                        $record = new AuditFinding([
                            'user_id' => $user->id,
                            'fingerprint' => $fingerprint,
                            'status' => AuditFinding::STATUS_OPEN,
                            'first_detected_at' => now(),
                        ]);
                    }

                    /*
                     * Reopen findings that had previously disappeared
                     * and have now returned.
                     */
                    if (
                        $record->exists
                        && $record->status
                            === AuditFinding::STATUS_RESOLVED
                    ) {
                        $record->status =
                            AuditFinding::STATUS_OPEN;

                        $record->resolved_at = null;
                        $record->reviewed_at = null;
                        $record->dismissed_at = null;
                    }

                    $record->fill([
                        'category' =>
                            $finding['category'] ?? 'other',

                        'title' =>
                            $finding['title']
                            ?? 'Portfolio finding',

                        'description' =>
                            $finding['description']
                            ?? 'No description available.',

                        'recommendation' =>
                            $finding['recommendation']
                            ?? null,

                        'severity' =>
                            $finding['severity']
                            ?? 'information',

                        'score' =>
                            $finding['score']
                            ?? null,

                        'route_name' =>
                            $finding['route']
                            ?? null,

                        'last_detected_at' => now(),

                        'metadata' => [
                            'formula_version' =>
                                AdvisorAuditService::FORMULA_VERSION,

                            'source' => 'advisor_audit',

                            'synced_at' =>
                                now()->toIso8601String(),
                        ],
                    ]);

                    $record->save();

                    $savedFindings->push($record);
                }

                /*
                 * Any previously active finding that was not detected
                 * during this calculation is marked resolved.
                 */
                AuditFinding::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', [
                        AuditFinding::STATUS_OPEN,
                        AuditFinding::STATUS_REVIEWED,
                    ])
                    ->when(
                        $detectedFingerprints->isNotEmpty(),
                        fn ($query) => $query->whereNotIn(
                            'fingerprint',
                            $detectedFingerprints->all(),
                        ),
                    )
                    ->update([
                        'status' =>
                            AuditFinding::STATUS_RESOLVED,

                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ]);

                return $savedFindings;
            },
        );
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function fingerprint(
        array $finding,
    ): string {
        $category = Str::lower(
            (string) ($finding['category'] ?? 'other'),
        );

        $title = Str::of(
            (string) ($finding['title'] ?? 'finding'),
        )
            ->lower()
            ->squish()
            ->toString();

        /*
         * Remove changing dollar amounts, percentages and numbers
         * so the same issue does not create a new record each time
         * its measured value changes.
         */
        $descriptionPattern = Str::of(
            (string) (
                $finding['description']
                ?? ''
            ),
        )
            ->lower()
            ->squish()
            ->toString();

        $descriptionPattern = preg_replace(
            [
                '/\$[\d,]+(?:\.\d+)?/',
                '/\d+(?:\.\d+)?%/',
                '/\b\d+(?:\.\d+)?\b/',
            ],
            '#',
            $descriptionPattern,
        ) ?? $descriptionPattern;

        return hash(
            'sha256',
            implode('|', [
                $category,
                $title,
                $descriptionPattern,
            ]),
        );
    }
}