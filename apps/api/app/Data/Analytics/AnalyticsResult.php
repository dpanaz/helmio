<?php

namespace App\Data\Analytics;

use JsonSerializable;

final readonly class AnalyticsResult implements JsonSerializable
{
    /**
     * @param array<string, mixed> $metrics
     * @param array<int, array<string, mixed>> $flags
     * @param array<int, array<string, mixed>> $warnings
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $status,
        public ?string $message,
        public ?int $score,
        public ?string $label,
        public array $metrics,
        public array $flags,
        public array $warnings,
        public array $data,
        public string $formulaVersion,
    ) {
    }

    /**
     * @param array<string, mixed> $metrics
     * @param array<int, array<string, mixed>> $flags
     * @param array<int, array<string, mixed>> $warnings
     * @param array<string, mixed> $data
     */
    public static function complete(
        array $metrics = [],
        array $flags = [],
        array $warnings = [],
        array $data = [],
        ?int $score = null,
        ?string $label = null,
        string $formulaVersion = 'unknown',
    ): self {
        return new self(
            status: 'complete',
            message: null,
            score: self::normalizeScore($score),
            label: $label,
            metrics: $metrics,
            flags: array_values($flags),
            warnings: array_values($warnings),
            data: $data,
            formulaVersion: $formulaVersion,
        );
    }

    /**
     * @param array<string, mixed> $metrics
     * @param array<int, array<string, mixed>> $flags
     * @param array<int, array<string, mixed>> $warnings
     * @param array<string, mixed> $data
     */
    public static function insufficientData(
        string $message,
        array $metrics = [],
        array $flags = [],
        array $warnings = [],
        array $data = [],
        string $formulaVersion = 'unknown',
    ): self {
        return new self(
            status: 'insufficient_data',
            message: $message,
            score: null,
            label: 'Insufficient data',
            metrics: $metrics,
            flags: array_values($flags),
            warnings: array_values($warnings),
            data: $data,
            formulaVersion: $formulaVersion,
        );
    }

    /**
     * @param array<string, mixed> $metrics
     * @param array<int, array<string, mixed>> $flags
     * @param array<int, array<string, mixed>> $warnings
     * @param array<string, mixed> $data
     */
    public static function failed(
        string $message,
        array $metrics = [],
        array $flags = [],
        array $warnings = [],
        array $data = [],
        string $formulaVersion = 'unknown',
    ): self {
        return new self(
            status: 'failed',
            message: $message,
            score: null,
            label: 'Calculation failed',
            metrics: $metrics,
            flags: array_values($flags),
            warnings: array_values($warnings),
            data: $data,
            formulaVersion: $formulaVersion,
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function hasScore(): bool
    {
        return $this->score !== null;
    }

    /**
     * Add or replace the score and label without changing the
     * underlying analytics output.
     */
    public function withScore(
        ?int $score,
        ?string $label = null,
    ): self {
        $score = self::normalizeScore($score);

        return new self(
            status: $this->status,
            message: $this->message,
            score: $score,
            label: $label ?? (
                $score !== null
                    ? self::scoreLabel($score)
                    : $this->label
            ),
            metrics: $this->metrics,
            flags: $this->flags,
            warnings: $this->warnings,
            data: $this->data,
            formulaVersion: $this->formulaVersion,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withData(array $data): self
    {
        return new self(
            status: $this->status,
            message: $this->message,
            score: $this->score,
            label: $this->label,
            metrics: $this->metrics,
            flags: $this->flags,
            warnings: $this->warnings,
            data: array_merge(
                $this->data,
                $data
            ),
            formulaVersion: $this->formulaVersion,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'score' => $this->score,
            'label' => $this->label,
            'metrics' => $this->metrics,
            'flags' => $this->flags,
            'warnings' => $this->warnings,
            'data' => $this->data,
            'formula_version' => $this->formulaVersion,
        ];
    }

    /**
     * Preserve the existing JSON response behavior.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function normalizeScore(
        ?int $score
    ): ?int {
        if ($score === null) {
            return null;
        }

        return max(
            0,
            min(100, $score)
        );
    }

    private static function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Very good',
            $score >= 70 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs attention',
            default => 'Action recommended',
        };
    }
}