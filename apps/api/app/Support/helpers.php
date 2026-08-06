<?php

if (! function_exists('money')) {
    function money(
        int|float|string|null $amount,
        int $decimals = 2
    ): string {
        return '$' . number_format(
            (float) ($amount ?? 0),
            $decimals
        );
    }
}