<?php

namespace App\Services\AdvisorAudit;

use App\Models\AuditFinding;
use Illuminate\Support\Collection;

class AdvisorActionCenterService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $userId): array
    {
        $findings = AuditFinding::query()
            ->where('user_id', $userId)
            ->whereIn('status', [
                AuditFinding::STATUS_OPEN,
                AuditFinding::STATUS_REVIEWED,
            ])
            ->orderByRaw(
                "CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'moderate' THEN 3
                    WHEN 'low' THEN 4
                    WHEN 'information' THEN 5
                    WHEN 'informational' THEN 5
                    WHEN 'positive' THEN 6
                    ELSE 7
                END"
            )
            ->orderByDesc('score')
            ->orderByDesc('last_detected_at')
            ->get();

        $normalized = $findings
            ->map(
                fn (AuditFinding $finding): array =>
                    $this->normalizeFinding($finding)
            )
            ->values();

        $critical = $normalized
            ->where('severity', 'critical')
            ->values();

        $important = $normalized
            ->whereIn('severity', [
                'high',
                'medium',
                'moderate',
            ])
            ->values();

        $opportunities = $normalized
            ->whereIn('severity', [
                'positive',
                'information',
                'informational',
            ])
            ->values();

        $totalFinancialImpact = $normalized
            ->sum(
                fn (array $finding): float =>
                    (float) (
                        $finding['financial_impact']
                        ?? 0
                    )
            );

        return [
            'summary' => [
                'total_count' =>
                    $normalized->count(),

                'critical_count' =>
                    $critical->count(),

                'important_count' =>
                    $important->count(),

                'opportunity_count' =>
                    $opportunities->count(),

                'reviewed_count' =>
                    $normalized
                        ->where(
                            'status',
                            AuditFinding::STATUS_REVIEWED
                        )
                        ->count(),

                'estimated_financial_impact' =>
                    round(
                        $totalFinancialImpact,
                        2
                    ),
            ],

            'critical' =>
                $critical->all(),

            'important' =>
                $important->all(),

            'opportunities' =>
                $opportunities->all(),

            'all' =>
                $normalized->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFinding(
        AuditFinding $finding
    ): array {
        $metadata =
            $finding->metadata ?? [];

        return [
            'id' =>
                $finding->id,

            'fingerprint' =>
                $finding->fingerprint,

            'category' =>
                $finding->category,

            'category_label' =>
                $metadata['category_label']
                ?? $this->categoryLabel(
                    $finding->category
                ),

            'title' =>
                $finding->title,

            'description' =>
                $finding->description,

            'recommendation' =>
                $finding->recommendation,

            'severity' =>
                $finding->severity,

            'severity_label' =>
                $this->severityLabel(
                    $finding->severity
                ),

            'status' =>
                $finding->status,

            'score' =>
                $finding->score,

            'priority' =>
                $metadata['priority']
                ?? $finding->score,

            'financial_impact' =>
                isset(
                    $metadata[
                        'financial_impact'
                    ]
                )
                && is_numeric(
                    $metadata[
                        'financial_impact'
                    ]
                )
                    ? (float) $metadata[
                        'financial_impact'
                    ]
                    : null,

            'confidence' =>
                $metadata['confidence']
                ?? null,

            'code' =>
                $metadata['code']
                ?? null,

            'type' =>
                $metadata['type']
                ?? null,

            'route_name' =>
                $finding->route_name
                ?? $this->routeForCategory(
                    $finding->category
                ),

            'first_detected_at' =>
                $finding
                    ->first_detected_at
                    ?->toIso8601String(),

            'last_detected_at' =>
                $finding
                    ->last_detected_at
                    ?->toIso8601String(),

            'reviewed_at' =>
                $finding
                    ->reviewed_at
                    ?->toIso8601String(),

            'review_notes' =>
                $finding->review_notes,
        ];
    }

    private function categoryLabel(
        string $category
    ): string {
        return match ($category) {
            'cost' =>
                'Cost',

            'diversification' =>
                'Diversification',

            'performance' =>
                'Performance',

            'risk' =>
                'Risk',

            'trading' =>
                'Trading Discipline',

            'cash' =>
                'Cash Drag',

            'tax' =>
                'Tax Efficiency',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $category
                    )
                ),
        };
    }

    private function severityLabel(
        string $severity
    ): string {
        return match ($severity) {
            'critical' =>
                'Critical',

            'high' =>
                'High',

            'medium',
            'moderate' =>
                'Important',

            'positive' =>
                'Opportunity',

            'information',
            'informational' =>
                'Information',

            default =>
                ucfirst($severity),
        };
    }

    private function routeForCategory(
        string $category
    ): string {
        return match ($category) {
            'cost' =>
                'analytics.costs',

            'diversification' =>
                'analytics.diversification',

            'performance' =>
                'analytics.performance',

            'risk' =>
                'analytics.risk',

            'trading' =>
                'analytics.trading-discipline',

            'cash' =>
                'analytics.cash-drag',

            'tax' =>
                'analytics.tax-efficiency',

            default =>
                'advisor-audit.index',
        };
    }
}