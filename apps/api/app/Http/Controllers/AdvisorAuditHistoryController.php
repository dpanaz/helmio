<?php

namespace App\Http\Controllers;

use App\Models\AuditRun;
use App\Services\Audit\AuditHistoryComparisonService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorAuditHistoryController extends Controller
{
    public function index(
        Request $request,
        AuditHistoryComparisonService $comparisonService,
    ): View {
        $runs = AuditRun::query()
            ->where('user_id', $request->user()->id)
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->get();

        $current = $runs->first();
        $previous = $runs->skip(1)->first();

        $comparison = $current !== null
            ? $comparisonService->compare(
                $current,
                $previous,
            )
            : null;

        return view('audit.history', [
            'runs' => $runs,
            'currentRun' => $current,
            'previousRun' => $previous,
            'comparison' => $comparison,
        ]);
    }

    public function show(
        Request $request,
        AuditRun $auditRun,
        AuditHistoryComparisonService $comparisonService,
    ): View {
        abort_unless(
            $auditRun->user_id
                === $request->user()->id,
            403,
        );

        $auditRun->load('findings');

        $previous = AuditRun::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($auditRun): void {
                $query
                    ->where(
                        'calculated_for_date',
                        '<',
                        $auditRun->calculated_for_date,
                    )
                    ->orWhere(function ($query) use ($auditRun): void {
                        $query
                            ->whereDate(
                                'calculated_for_date',
                                $auditRun->calculated_for_date,
                            )
                            ->where('id', '<', $auditRun->id);
                    });
            })
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->first();

        return view('audit.history-show', [
            'run' => $auditRun,
            'previousRun' => $previous,
            'comparison' =>
                $comparisonService->compare(
                    $auditRun,
                    $previous,
                ),
        ]);
    }
}
