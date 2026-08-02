<?php

namespace App\Services\Audit;

use App\Models\AuditFinding;
use App\Models\InvestmentAccount;
use App\Models\User;

class AdvisorAuditReportDataService
{
    public function __construct(
        private readonly AdvisorAuditService $auditService,
        private readonly AuditFindingSyncService $syncService,
        private readonly AuditRunRecorderService $runRecorder,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'institution',
                'holdings.security',
                'transactions.security',
                'portfolioSnapshots',
                'benchmark.returns',
            ])
            ->orderBy('name')
            ->get();

        $audit = $this->auditService->build(
            $accounts,
        );

        $this->syncService->sync(
            $user,
            $audit['findings'],
        );

        $findings = AuditFinding::query()
            ->where('user_id', $user->id)
            ->orderByRaw(
                "CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    WHEN 'information' THEN 5
                    WHEN 'positive' THEN 6
                    ELSE 7
                END",
            )
            ->orderByDesc('last_detected_at')
            ->get();

        $this->runRecorder->record(
            $user,
            $audit,
            $findings,
        );

        return [
            'user' => $user,
            'accounts' => $accounts,
            'audit' => $audit,
            'findings' => $findings,
            'generatedAt' => now(),
        ];
    }
}
