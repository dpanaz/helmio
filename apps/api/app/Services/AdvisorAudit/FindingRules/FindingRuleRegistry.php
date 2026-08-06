<?php

namespace App\Services\AdvisorAudit\FindingRules;

class FindingRuleRegistry
{
    /**
     * @var array<int, CategoryFindingRules>
     */
    private array $rules;

    public function __construct(
        SuitabilityFindingRules $suitabilityRules,
        DefaultFindingRules $defaultRules,
    ) {
        $this->rules = [
            $suitabilityRules,
            $defaultRules,
        ];
    }

    public function for(
        string $category
    ): CategoryFindingRules {
        foreach ($this->rules as $rules) {
            if ($rules->supports($category)) {
                return $rules;
            }
        }

        throw new \RuntimeException(
            "No finding rules support category [{$category}]."
        );
    }
}