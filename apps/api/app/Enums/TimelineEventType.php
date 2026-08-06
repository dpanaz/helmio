<?php

namespace App\Enums;

enum TimelineEventType: string
{
    case HoldingAdded = 'holding_added';
    case HoldingRemoved = 'holding_removed';
    case WeightIncrease = 'weight_increase';
    case WeightDecrease = 'weight_decrease';

    case PortfolioValueIncrease = 'portfolio_value_increase';
    case PortfolioValueDecrease = 'portfolio_value_decrease';

    case CostIncrease = 'cost_increase';
    case CostDecrease = 'cost_decrease';

    case PotentialSavingsIncrease = 'potential_savings_increase';
    case PotentialSavingsDecrease = 'potential_savings_decrease';

    case DiversificationImproved = 'diversification_improved';
    case DiversificationDeclined = 'diversification_declined';

    case CategoryScoreImproved = 'category_score_improved';
    case CategoryScoreDeclined = 'category_score_declined';

    case HelmScoreImproved = 'helm_score_improved';
    case HelmScoreDeclined = 'helm_score_declined';

    case AuditGradeImproved = 'audit_grade_improved';
    case AuditGradeDeclined = 'audit_grade_declined';

    case LargeDeposit = 'large_deposit';
    case LargeWithdrawal = 'large_withdrawal';
    case TradingSpike = 'trading_spike';
}