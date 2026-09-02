<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingConversion;
use App\Models\MarketingVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedditCampaignController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $days = (int) $request->integer(
            'days',
            30,
        );

        if (
            ! in_array(
                $days,
                [7, 30, 90, 365],
                true,
            )
        ) {
            $days = 30;
        }

        $since = now()
            ->subDays($days)
            ->startOfDay();

        $visits = MarketingVisit::query()
            ->where('source', 'reddit')
            ->where(
                'first_seen_at',
                '>=',
                $since,
            );

        $conversions = MarketingConversion::query()
            ->where(
                'converted_at',
                '>=',
                $since,
            )
            ->whereHas(
                'visit',
                fn (Builder $query) =>
                    $query->where(
                        'source',
                        'reddit',
                    ),
            );

        $visitors = (clone $visits)
            ->distinct()
            ->count('visitor_uuid');

        $signups = $this->conversionUsers(
            clone $conversions,
            'SIGN_UP',
        );

        $connectedAccounts =
            $this->conversionUsers(
                clone $conversions,
                'AccountConnected',
            );

        $auditsCompleted =
            $this->conversionUsers(
                clone $conversions,
                'AuditCompleted',
            );

        $paidSubscribers =
            $this->conversionUsers(
                clone $conversions,
                'PURCHASE',
            );

        $purchases = (clone $conversions)
            ->where('type', 'PURCHASE')
            ->count();

        $revenue = (float) (
            clone $conversions
        )
            ->where('type', 'PURCHASE')
            ->sum('value');

        $campaigns = (clone $visits)
            ->select([
                'campaign',
                'content',
            ])
            ->selectRaw(
                'COUNT(DISTINCT visitor_uuid) AS visitors',
            )
            ->groupBy([
                'campaign',
                'content',
            ])
            ->orderByDesc('visitors')
            ->get()
            ->map(function (
                MarketingVisit $campaign,
            ) use ($since): array {
                $campaignConversions =
                    MarketingConversion::query()
                        ->where(
                            'converted_at',
                            '>=',
                            $since,
                        )
                        ->whereHas(
                            'visit',
                            function (
                                Builder $query,
                            ) use ($campaign): void {
                                $query->where(
                                    'source',
                                    'reddit',
                                );

                                $this
                                    ->applyNullableFilter(
                                        $query,
                                        'campaign',
                                        $campaign->campaign,
                                    );

                                $this
                                    ->applyNullableFilter(
                                        $query,
                                        'content',
                                        $campaign->content,
                                    );
                            },
                        );

                $visitors = (int) (
                    $campaign->visitors
                );

                $signups =
                    $this->conversionUsers(
                        clone $campaignConversions,
                        'SIGN_UP',
                    );

                $connected =
                    $this->conversionUsers(
                        clone $campaignConversions,
                        'AccountConnected',
                    );

                $paid =
                    $this->conversionUsers(
                        clone $campaignConversions,
                        'PURCHASE',
                    );

                $revenue = (float) (
                    clone $campaignConversions
                )
                    ->where(
                        'type',
                        'PURCHASE',
                    )
                    ->sum('value');

                return [
                    'campaign' =>
                        $campaign->campaign
                        ?: 'Unspecified',

                    'content' =>
                        $campaign->content
                        ?: 'Unspecified',

                    'visitors' =>
                        $visitors,

                    'signups' =>
                        $signups,

                    'connected_accounts' =>
                        $connected,

                    'paid_subscribers' =>
                        $paid,

                    'revenue' =>
                        $revenue,

                    'signup_rate' =>
                        $this->rate(
                            $signups,
                            $visitors,
                        ),

                    'connection_rate' =>
                        $this->rate(
                            $connected,
                            $visitors,
                        ),

                    'paid_rate' =>
                        $this->rate(
                            $paid,
                            $visitors,
                        ),
                ];
            })
            ->values();

        return view(
            'admin.marketing.reddit',
            [
                'days' => $days,

                'metrics' => [
                    'visitors' =>
                        $visitors,

                    'signups' =>
                        $signups,

                    'connected_accounts' =>
                        $connectedAccounts,

                    'audits_completed' =>
                        $auditsCompleted,

                    'paid_subscribers' =>
                        $paidSubscribers,

                    'purchases' =>
                        $purchases,

                    'revenue' =>
                        $revenue,

                    'signup_rate' =>
                        $this->rate(
                            $signups,
                            $visitors,
                        ),

                    'connection_rate' =>
                        $this->rate(
                            $connectedAccounts,
                            $visitors,
                        ),

                    'paid_rate' =>
                        $this->rate(
                            $paidSubscribers,
                            $visitors,
                        ),
                ],

                'campaigns' =>
                    $campaigns,
            ],
        );
    }

    private function conversionUsers(
        Builder $query,
        string $type,
    ): int {
        return $query
            ->where('type', $type)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');
    }

    private function rate(
        int $conversions,
        int $visitors,
    ): float {
        if ($visitors === 0) {
            return 0;
        }

        return round(
            ($conversions / $visitors) * 100,
            1,
        );
    }

    private function applyNullableFilter(
        Builder $query,
        string $column,
        ?string $value,
    ): void {
        if ($value === null) {
            $query->whereNull($column);

            return;
        }

        $query->where(
            $column,
            $value,
        );
    }
}