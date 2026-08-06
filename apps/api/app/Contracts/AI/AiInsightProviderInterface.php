<?php

namespace App\Contracts\AI;

interface AiInsightProviderInterface
{
    public function providerName(): string;

    public function modelName(): ?string;

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function generate(
        array $context,
    ): array;
}