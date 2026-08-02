<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Audit\AdvisorAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorAuditController extends Controller
{
    public function index(
        Request $request,
        AdvisorAuditService $auditService,
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

        return view('audit.advisor', [
            'audit' => $auditService->build(
                $accounts,
            ),
        ]);
    }
}
