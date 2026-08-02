<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\InvestmentAccount;
use App\Services\Audit\AdvisorAuditService;
use App\Services\Audit\AuditFindingSyncService;
use App\Services\Audit\AuditRunRecorderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorAuditController extends Controller
{
    public function index(
        Request $request,
        AdvisorAuditService $auditService,
        AuditFindingSyncService $syncService,
        AuditRunRecorderService $runRecorder,
    ): View {
        $user = $request->user();

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

        $audit = $auditService->build($accounts);

        /*
         * Synchronize the latest calculated findings with the
         * persistent audit_findings table.
         */
        $syncService->sync(
            $user,
            $audit['findings'],
        );

        /*
         * Reload all persistent findings after synchronization so the
         * page shows current statuses, timestamps and review notes.
         */
        $persistentFindings = AuditFinding::query()
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
            ->orderByRaw(
                "CASE status
                    WHEN 'open' THEN 1
                    WHEN 'reviewed' THEN 2
                    WHEN 'dismissed' THEN 3
                    WHEN 'resolved' THEN 4
                    ELSE 5
                END",
            )
            ->orderByDesc('last_detected_at')
            ->orderByDesc('id')
            ->get();

        /*
         * Record or refresh today's audit snapshot. The recorder uses
         * user, date and formula version to avoid duplicate daily runs.
         */
        $currentRun = $runRecorder->record(
            $user,
            $audit,
            $persistentFindings,
        );

        $openFindingCount = $persistentFindings
            ->where(
                'status',
                AuditFinding::STATUS_OPEN,
            )
            ->count();

        $reviewedFindingCount = $persistentFindings
            ->where(
                'status',
                AuditFinding::STATUS_REVIEWED,
            )
            ->count();

        $dismissedFindingCount = $persistentFindings
            ->where(
                'status',
                AuditFinding::STATUS_DISMISSED,
            )
            ->count();

        $resolvedFindingCount = $persistentFindings
            ->where(
                'status',
                AuditFinding::STATUS_RESOLVED,
            )
            ->count();

        $activeFindingCount = $persistentFindings
            ->whereIn('status', [
                AuditFinding::STATUS_OPEN,
                AuditFinding::STATUS_REVIEWED,
            ])
            ->count();

        return view('audit.advisor', [
            'audit' => $audit,
            'accounts' => $accounts,
            'currentRun' => $currentRun,

            'persistentFindings' =>
                $persistentFindings,

            'openFindingCount' =>
                $openFindingCount,

            'reviewedFindingCount' =>
                $reviewedFindingCount,

            'dismissedFindingCount' =>
                $dismissedFindingCount,

            'resolvedFindingCount' =>
                $resolvedFindingCount,

            'activeFindingCount' =>
                $activeFindingCount,
        ]);
    }
}