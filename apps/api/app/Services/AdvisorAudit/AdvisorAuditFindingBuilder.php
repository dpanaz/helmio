<?php

namespace App\Services\AdvisorAudit;

use App\Services\AdvisorAudit\FindingRules\CategoryFindingRules;
use App\Services\AdvisorAudit\FindingRules\DefaultFindingRules;
use App\Services\AdvisorAudit\FindingRules\FindingRuleRegistry;
use App\Services\AdvisorAudit\FindingRules\SuitabilityFindingRules;

class AdvisorAuditFindingBuilder
{
    public const FORMULA_VERSION =
        'advisor-audit-findings-0.2.0';

    private FindingRuleRegistry $ruleRegistry;

    public function __construct(
        ?FindingRuleRegistry $ruleRegistry = null,
    ) {
        $this->ruleRegistry =
            $ruleRegistry
            ?? new FindingRuleRegistry(
                new SuitabilityFindingRules(),
                new DefaultFindingRules(),
            );
    }

    /**
     * @param array<string, array<string, mixed>> $categories
     * @return array<string, mixed>
     */
    public function build(array $categories): array
    {
        $findings = [];

        foreach ($categories as $category => $result) {
            $rules = $this->ruleRegistry->for(
                $category
            );

            $findings = array_merge(
                $findings,

                $this->findingsFromFlags(
                    category: $category,
                    flags: $result['flags'] ?? [],
                    score: $result['score'] ?? null,
                    rules: $rules,
                ),

                $this->findingsFromWarnings(
                    category: $category,
                    warnings: $result['warnings'] ?? [],
                    rules: $rules,
                ),

                $this->findingsFromReasons(
                    category: $category,
                    reasons: $result['reasons'] ?? [],
                    score: $result['score'] ?? null,
                    rules: $rules,
                ),

                $this->findingsFromRecommendations(
                    category: $category,
                    recommendations:
                        $result['recommendations'] ?? [],
                    score: $result['score'] ?? null,
                    rules: $rules,
                ),
            );
        }

        $findings = $this->deduplicate(
            $findings
        );

        usort(
            $findings,
            fn (array $left, array $right): int =>
                $right['priority']
                <=> $left['priority']
        );

        $critical = [];
        $important = [];
        $opportunities = [];
        $recommendations = [];

        foreach ($findings as $finding) {
            if ($finding['type'] === 'recommendation') {
                $recommendations[] = $finding;
                continue;
            }

            if ($finding['type'] === 'opportunity') {
                $opportunities[] = $finding;
                continue;
            }

            if ($finding['severity'] === 'critical') {
                $critical[] = $finding;
            } else {
                $important[] = $finding;
            }
        }

        return [
            'critical' => array_values($critical),
            'important' => array_values($important),
            'opportunities' => array_values($opportunities),
            'recommendations' => array_values($recommendations),
            'all' => array_values($findings),

            'summary' => [
                'critical_count' => count($critical),
                'important_count' => count($important),
                'opportunity_count' => count($opportunities),
                'recommendation_count' => count($recommendations),
                'total_finding_count' => count($findings),
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $flags
     * @return array<int, array<string, mixed>>
     */
    private function findingsFromFlags(
        string $category,
        array $flags,
        mixed $score,
        CategoryFindingRules $rules,
    ): array {
        $findings = [];

        foreach ($flags as $flag) {
            $code =
                $flag['code']
                ?? 'analytics_flag';

            $severity =
                $rules->normalizeSeverity(
                    $code,
                    $flag['severity'] ?? null
                );

            $type =
                $rules->typeForCode(
                    $code,
                    $severity
                );

            $financialImpact =
                $this->financialImpact($flag);

            $findings[] = [
                'id' =>
                    $this->findingId(
                        $category,
                        $code,
                        $flag['message'] ?? ''
                    ),

                'category' =>
                    $category,

                'category_label' =>
                    $rules->categoryLabel(
                        $category
                    ),

                'code' =>
                    $code,

                'type' =>
                    $type,

                'severity' =>
                    $severity,

                'priority' =>
                    $this->priority(
                        category: $category,
                        severity: $severity,
                        type: $type,
                        code: $code,
                        score: $score,
                        financialImpact:
                            $financialImpact,
                        rules: $rules,
                    ),

                'title' =>
                    $rules->title(
                        code: $code,
                        category: $category,
                        providedTitle:
                            $flag['title'] ?? null,
                    ),

                'message' =>
                    $rules->message(
                        code: $code,
                        category: $category,
                        providedMessage:
                            $flag['message'] ?? null,
                    ),

                'recommendation' =>
                    $flag['recommendation']
                    ?? $rules->recommendation(
                        $code,
                        $category
                    ),

                'financial_impact' =>
                    $financialImpact,

                'confidence' =>
                    $flag['confidence']
                    ?? 'medium',

                'source' =>
                    'flag',
            ];
        }

        return $findings;
    }

    /**
     * @param array<int, array<string, mixed>> $warnings
     * @return array<int, array<string, mixed>>
     */
    private function findingsFromWarnings(
        string $category,
        array $warnings,
        CategoryFindingRules $rules,
    ): array {
        $findings = [];

        foreach ($warnings as $warning) {
            $code =
                $warning['code']
                ?? 'data_warning';

            $message =
                $warning['message']
                ?? 'Data quality may limit this analysis.';

            $findings[] = [
                'id' =>
                    $this->findingId(
                        $category,
                        $code,
                        $message
                    ),

                'category' =>
                    $category,

                'category_label' =>
                    $rules->categoryLabel(
                        $category
                    ),

                'code' =>
                    $code,

                'type' =>
                    'data_quality',

                'severity' =>
                    'informational',

                'priority' =>
                    $this->priority(
                        category: $category,
                        severity: 'informational',
                        type: 'data_quality',
                        code: $code,
                        score: null,
                        financialImpact: null,
                        rules: $rules,
                    ),

                'title' =>
                    $rules->title(
                        code: $code,
                        category: $category,
                        providedTitle:
                            $warning['title']
                            ?? 'Data quality limitation',
                    ),

                'message' =>
                    $message,

                'recommendation' =>
                    $rules->recommendation(
                        $code,
                        $category
                    ),

                'financial_impact' =>
                    null,

                'confidence' =>
                    'high',

                'source' =>
                    'warning',
            ];
        }

        return $findings;
    }

    /**
     * @param array<int, string> $reasons
     * @return array<int, array<string, mixed>>
     */
    private function findingsFromReasons(
        string $category,
        array $reasons,
        mixed $score,
        CategoryFindingRules $rules,
    ): array {
        $findings = [];

        foreach ($reasons as $index => $reason) {
            if (! is_string($reason)) {
                continue;
            }

            $severity =
                $this->severityFromScore(
                    $score
                );

            $code =
                'category_reason_'.$index;

            $financialImpact =
                $this->extractCurrency(
                    $reason
                );

            $findings[] = [
                'id' =>
                    $this->findingId(
                        $category,
                        $code,
                        $reason
                    ),

                'category' =>
                    $category,

                'category_label' =>
                    $rules->categoryLabel(
                        $category
                    ),

                'code' =>
                    $code,

                'type' =>
                    'concern',

                'severity' =>
                    $severity,

                'priority' =>
                    $this->priority(
                        category: $category,
                        severity: $severity,
                        type: 'concern',
                        code: $code,
                        score: $score,
                        financialImpact:
                            $financialImpact,
                        rules: $rules,
                    ),

                'title' =>
                    $rules->categoryLabel(
                        $category
                    ).' observation',

                'message' =>
                    $reason,

                'recommendation' =>
                    null,

                'financial_impact' =>
                    $financialImpact,

                'confidence' =>
                    'medium',

                'source' =>
                    'reason',
            ];
        }

        return $findings;
    }

    /**
     * @param array<int, string> $recommendations
     * @return array<int, array<string, mixed>>
     */
    private function findingsFromRecommendations(
        string $category,
        array $recommendations,
        mixed $score,
        CategoryFindingRules $rules,
    ): array {
        $findings = [];

        foreach ($recommendations as $index => $recommendation) {
            if (! is_string($recommendation)) {
                continue;
            }

            $code =
                'recommendation_'.$index;

            $financialImpact =
                $this->extractCurrency(
                    $recommendation
                );

            $findings[] = [
                'id' =>
                    $this->findingId(
                        $category,
                        $code,
                        $recommendation
                    ),

                'category' =>
                    $category,

                'category_label' =>
                    $rules->categoryLabel(
                        $category
                    ),

                'code' =>
                    $code,

                'type' =>
                    'recommendation',

                'severity' =>
                    'informational',

                'priority' =>
                    $this->priority(
                        category: $category,
                        severity: 'informational',
                        type: 'recommendation',
                        code: $code,
                        score: $score,
                        financialImpact:
                            $financialImpact,
                        rules: $rules,
                    ),

                'title' =>
                    $rules->categoryLabel(
                        $category
                    ).' recommendation',

                'message' =>
                    $recommendation,

                'recommendation' =>
                    $recommendation,

                'financial_impact' =>
                    $financialImpact,

                'confidence' =>
                    'medium',

                'source' =>
                    'recommendation',
            ];
        }

        return $findings;
    }

    private function priority(
        string $category,
        string $severity,
        string $type,
        string $code,
        mixed $score,
        ?float $financialImpact,
        CategoryFindingRules $rules,
    ): int {
        $priority =
            $this->severityWeight(
                $severity
            );

        $priority +=
            $rules->categoryWeight(
                $category
            );

        $priority +=
            $rules->codeWeight(
                $code
            );

        if (
            is_numeric($score)
            && (int) $score < 70
        ) {
            $priority += min(
                20,
                70 - (int) $score
            );
        }

        if ($financialImpact !== null) {
            $priority += match (true) {
                $financialImpact >= 25000 => 25,
                $financialImpact >= 10000 => 20,
                $financialImpact >= 5000 => 15,
                $financialImpact >= 1000 => 10,
                $financialImpact >= 250 => 5,
                default => 0,
            };
        }

        if ($type === 'opportunity') {
            $priority -= 5;
        }

        if ($type === 'recommendation') {
            $priority -= 15;
        }

        if ($type === 'data_quality') {
            $priority -= 20;
        }

        return max(
            0,
            min(100, $priority)
        );
    }

    private function severityWeight(
        string $severity
    ): int {
        return match ($severity) {
            'critical' => 70,
            'high' => 55,
            'moderate' => 40,
            'informational' => 20,
            default => 30,
        };
    }

    private function severityFromScore(
        mixed $score
    ): string {
        if (! is_numeric($score)) {
            return 'informational';
        }

        return match (true) {
            (int) $score < 40 => 'critical',
            (int) $score < 60 => 'high',
            (int) $score < 75 => 'moderate',
            default => 'informational',
        };
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function financialImpact(
        array $finding
    ): ?float {
        foreach (
            [
                'financial_impact',
                'estimated_impact',
                'amount',
                'opportunity_cost',
                'estimated_disallowed_loss',
                'estimated_harvestable_loss',
            ] as $key
        ) {
            if (
                isset($finding[$key])
                && is_numeric($finding[$key])
            ) {
                return abs(
                    (float) $finding[$key]
                );
            }
        }

        return $this->extractCurrency(
            (string) (
                $finding['message']
                ?? ''
            )
        );
    }

    private function extractCurrency(
        string $message
    ): ?float {
        if (
            ! preg_match(
                '/\$\s*([\d,]+(?:\.\d{1,2})?)/',
                $message,
                $matches
            )
        ) {
            return null;
        }

        return (float) str_replace(
            ',',
            '',
            $matches[1]
        );
    }

    /**
     * @param array<int, array<string, mixed>> $findings
     * @return array<int, array<string, mixed>>
     */
    private function deduplicate(
        array $findings
    ): array {
        $deduplicated = [];

        foreach ($findings as $finding) {
            $key =
                $finding['category']
                .':'
                .strtolower(
                    trim(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            $finding['message']
                        ) ?? ''
                    )
                );

            if (
                ! isset($deduplicated[$key])
                || $finding['priority']
                    > $deduplicated[$key]['priority']
            ) {
                $deduplicated[$key] =
                    $finding;
            }
        }

        return array_values(
            $deduplicated
        );
    }

    private function findingId(
        string $category,
        string $code,
        string $message
    ): string {
        return substr(
            hash(
                'sha256',
                $category
                .'|'
                .$code
                .'|'
                .$message
            ),
            0,
            20
        );
    }
}