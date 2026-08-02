<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\InvestmentAccount;
use App\Services\Audit\AdvisorAuditService;
use App\Services\Audit\AuditFindingSyncService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorAuditController extends Controller
{
    public function index(
        Request $request,
        AdvisorAuditService $auditService,
        AuditFindingSyncService $syncService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with([
                'institution',
                'holdings.security',
                'transactions.security',
                'portfolioSnapshots',
                'benchmark.returns',
            ])
            ->orderBy('name')
            ->get();

        $audit = $auditService->build(
            $accounts,
        );

        $syncService->sync(
            $request->user(),
            $audit['findings'],
        );

        $persistentFindings = AuditFinding::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
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

        return view('audit.advisor', [
            'audit' => $audit,
            'persistentFindings' =>
                $persistentFindings,

            'openFindingCount' =>
                $persistentFindings
                    ->where(
                        'status',
                        AuditFinding::STATUS_OPEN,
                    )
                    ->count(),

            'reviewedFindingCount' =>
                $persistentFindings
                    ->where(
                        'status',
                        AuditFinding::STATUS_REVIEWED,
                    )
                    ->count(),

            'resolvedFindingCount' =>
                $persistentFindings
                    ->where(
                        'status',
                        AuditFinding::STATUS_RESOLVED,
                    )
                    ->count(),
        ]);
    }
}