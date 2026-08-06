<?php

namespace App\Services\AdvisorAudit\FindingRules;

interface CategoryFindingRules
{
    public function supports(string $category): bool;

    public function categoryLabel(string $category): string;

    public function categoryWeight(string $category): int;

    public function codeWeight(string $code): int;

    public function normalizeSeverity(
        string $code,
        ?string $severity
    ): string;

    public function typeForCode(
        string $code,
        string $severity
    ): string;

    public function title(
        string $code,
        string $category,
        ?string $providedTitle = null
    ): string;

    public function message(
        string $code,
        string $category,
        ?string $providedMessage = null
    ): string;

    public function recommendation(
        string $code,
        string $category
    ): ?string;
}