<?php

namespace App\Data\Simulation;

use InvalidArgumentException;

final readonly class PortfolioChangeData
{
    public const ACTION_BUY = 'buy';
    public const ACTION_SELL = 'sell';
    public const ACTION_REMOVE = 'remove';
    public const ACTION_SET_VALUE = 'set_value';
    public const ACTION_CHANGE_ADVISORY_FEE = 'change_advisory_fee';

    public function __construct(
        public string $action,
        public ?string $symbol = null,
        public ?float $amount = null,
        public ?float $percentage = null,
        public ?float $advisoryFeeRate = null,
        public ?SimulatedHoldingData $security = null,
    ) {
        if (! in_array($action, self::allowedActions(), true)) {
            throw new InvalidArgumentException(
                "Unsupported portfolio simulation action: {$action}"
            );
        }
    }

    public static function allowedActions(): array
    {
        return [
            self::ACTION_BUY,
            self::ACTION_SELL,
            self::ACTION_REMOVE,
            self::ACTION_SET_VALUE,
            self::ACTION_CHANGE_ADVISORY_FEE,
        ];
    }
}