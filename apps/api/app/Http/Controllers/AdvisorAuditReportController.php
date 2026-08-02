<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\InvestmentAccount;
use App\Services\Audit\AdvisorAuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdvisorAuditReportController extends Controller
{
    public function show(
        Request $request,
        AdvisorAuditService $auditService,
    ): View {
        return view(
            'audit.report',
            $this->reportData(
                $request,
                $auditService,
            ),
        );
    }

    public function download(
        Request $request,
        AdvisorAuditService $auditService,
    ): SymfonyResponse {
        $data = $this->reportData(
            $request,
            $auditService,
        );

        $fileName = sprintf(
            'helmio-advisor-audit-%s.pdf',
            now()->format('Y-m-d'),
        );

        return Pdf::loadView(
            'audit.report-pdf',
            $data,
        )
            ->setPaper('letter', 'portrait')
            ->download($fileName);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(
        Request $request,
        AdvisorAuditService $auditService,
    ): array {
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

        $audit = $auditService->build($accounts);

        $findings = AuditFinding::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->whereIn('status', [
                AuditFinding::STATUS_OPEN,
                AuditFinding::STATUS_REVIEWED,
                AuditFinding::STATUS_DISMISSED,
                AuditFinding::STATUS_RESOLVED,
            ])
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

        return [
            'user' => $request->user(),
            'accounts' => $accounts,
            'audit' => $audit,
            'findings' => $findings,
            'generatedAt' => now(),
        ];
    }
}