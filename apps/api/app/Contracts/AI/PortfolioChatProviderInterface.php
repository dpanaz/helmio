<?php

namespace App\Contracts\AI;

interface PortfolioChatProviderInterface
{
    public function providerName(): string;

    public function modelName(): ?string;

    /**
     * @param array<string, mixed> $context
     * @param array<int, array<string, string>> $history
     * @return array<string, mixed>
     */
    public function answer(
        string $question,
        array $context,
        array $history = [],
    ): array;
}