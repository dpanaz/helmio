<?php

namespace App\Services\Analytics;

use App\Models\HelmScoreSnapshot;
use App\Models\User;
use App\Notifications\HelmScoreNotification;
use App\Services\Notifications\WebPushService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Throwable;

class HelmScoreNotificationService
{
    public function __construct(
        private readonly WebPushService $webPushService,
    ) {
    }

    public function generate(
        User $user,
        array $score,
        ?HelmScoreSnapshot $previousSnapshot = null,
    ): void {
        $overallScore =
            $score['overall_score']
            ?? null;

        if ($overallScore === null) {
            return;
        }

        $overallLabel =
            strtolower(
                (string) (
                    $score['overall_label']
                    ?? ''
                )
            );

        $importantFlags =
            $this->importantFlags(
                $score,
            );

        /*
         * First completed Helm Score.
         *
         * Send one summary notification rather than one
         * notification for every finding.
         */
        if ($previousSnapshot === null) {
            if (
                $overallLabel !== 'needs attention'
                && $importantFlags->isEmpty()
            ) {
                return;
            }

            $this->sendInitialNotification(
                user:
                    $user,

                score:
                    $score,

                importantFlags:
                    $importantFlags,
            );

            return;
        }

        $previousScore =
            $previousSnapshot->overall_score !== null
                ? (int) $previousSnapshot->overall_score
                : null;

        /*
         * Meaningful score decline.
         */
        if (
            $previousScore !== null
            && $overallScore <= $previousScore - 5
        ) {
            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        sprintf(
                            'helm-score:%s:declined:%s',
                            $score[
                                'calculated_for_date'
                            ],
                            $overallScore,
                        ),

                    'type' =>
                        'helm_score_declined',

                    'severity' =>
                        'high',

                    'title' =>
                        'Your Helm Score declined',

                    'message' =>
                        sprintf(
                            'Your Helm Score fell from %d to %d. Review what changed and which areas need attention.',
                            $previousScore,
                            $overallScore,
                        ),

                    'action_label' =>
                        'Review Helm Score',

                    'action_url' =>
                        route('dashboard'),

                    'helm_score' =>
                        $overallScore,

                    'previous_helm_score' =>
                        $previousScore,

                    'calculated_for_date' =>
                        $score[
                            'calculated_for_date'
                        ],
                ],
            );
        }

        /*
         * Notify only about new high/critical findings.
         */
        $previousFlags =
            $this->importantFlags(
                $previousSnapshot->score_details
                ?? []
            )
                ->keyBy('key');

        foreach ($importantFlags as $flag) {
            if (
                $previousFlags->has(
                    $flag['key']
                )
            ) {
                continue;
            }

            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        sprintf(
                            'helm-score:%s:new:%s',
                            $score[
                                'calculated_for_date'
                            ],
                            $flag['key'],
                        ),

                    'type' =>
                        'helm_finding_new',

                    'severity' =>
                        $flag['severity'],

                    'title' =>
                        'New Helmio finding',

                    'message' =>
                        $flag['title'],

                    'action_label' =>
                        'Review finding',

                    'action_url' =>
                        route('dashboard'),

                    'helm_score' =>
                        $overallScore,

                    'previous_helm_score' =>
                        $previousScore,

                    'calculated_for_date' =>
                        $score[
                            'calculated_for_date'
                        ],
                ],
            );
        }
    }

    private function sendInitialNotification(
        User $user,
        array $score,
        Collection $importantFlags,
    ): void {
        $overallScore =
            (int) $score['overall_score'];

        $criticalCount =
            $importantFlags
                ->where(
                    'severity',
                    'critical',
                )
                ->count();

        $highCount =
            $importantFlags
                ->where(
                    'severity',
                    'high',
                )
                ->count();

        $severity =
            $criticalCount > 0
                ? 'critical'
                : (
                    $highCount > 0
                        ? 'high'
                        : 'moderate'
                );

        $message =
            sprintf(
                'Your Helm Score is %d — %s.',
                $overallScore,
                $score['overall_label']
                ?? 'Review recommended',
            );

        if ($criticalCount > 0) {
            $message .= sprintf(
                ' Helmio found %d critical issue%s.',
                $criticalCount,
                $criticalCount === 1
                    ? ''
                    : 's',
            );
        } elseif ($highCount > 0) {
            $message .= sprintf(
                ' Helmio found %d high-priority issue%s worth reviewing.',
                $highCount,
                $highCount === 1
                    ? ''
                    : 's',
            );
        }

        $this->sendOnce(
            $user,
            [
                'event_key' =>
                    sprintf(
                        'helm-score:%s:initial',
                        $score[
                            'calculated_for_date'
                        ],
                    ),

                'type' =>
                    'helm_score_initial',

                'severity' =>
                    $severity,

                'title' =>
                    'Your portfolio needs attention',

                'message' =>
                    $message,

                'action_label' =>
                    'Review Helm Score',

                'action_url' =>
                    route('dashboard'),

                'helm_score' =>
                    $overallScore,

                'previous_helm_score' =>
                    null,

                'calculated_for_date' =>
                    $score[
                        'calculated_for_date'
                    ],
            ],
        );
    }

    private function importantFlags(
        array $score,
    ): Collection {
        $categories =
            collect(
                $score['categories']
                ?? []
            );

        return $categories
            ->flatMap(
                function (
                    array $category,
                    string $categoryName,
                ): array {
                    return collect(
                        $category['flags']
                        ?? []
                    )
                        ->filter(
                            fn (array $flag): bool =>
                                in_array(
                                    strtolower(
                                        (string) (
                                            $flag[
                                                'severity'
                                            ]
                                            ?? ''
                                        )
                                    ),
                                    [
                                        'critical',
                                        'high',
                                    ],
                                    true,
                                )
                        )
                        ->map(
                            function (
                                array $flag
                            ) use (
                                $categoryName
                            ): array {
                                $code =
                                    $flag['code']
                                    ?? md5(
                                        json_encode(
                                            $flag
                                        )
                                    );

                                return [
                                    'key' =>
                                        $categoryName
                                        . ':'
                                        . $code,

                                    'category' =>
                                        $categoryName,

                                    'code' =>
                                        $code,

                                    'severity' =>
                                        strtolower(
                                            (string) (
                                                $flag[
                                                    'severity'
                                                ]
                                                ?? 'high'
                                            )
                                        ),

                                    'title' =>
                                        $flag['title']
                                        ?? 'Portfolio finding',

                                    'message' =>
                                        $flag['message']
                                        ?? null,
                                ];
                            }
                        )
                        ->values()
                        ->all();
                }
            )
            ->values();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendOnce(
        User $user,
        array $data,
    ): void {
        /*
         * Prevent duplicate Helmio events from creating
         * duplicate database notifications or push alerts.
         */
        $alreadyExists =
            DatabaseNotification::query()
                ->where(
                    'notifiable_type',
                    $user->getMorphClass(),
                )
                ->where(
                    'notifiable_id',
                    $user->getKey(),
                )
                ->where(
                    'data->event_key',
                    $data['event_key'],
                )
                ->exists();

        if ($alreadyExists) {
            return;
        }

        /*
         * Always create the in-app/database notification first.
         */
        $user->notify(
            new HelmScoreNotification(
                $data,
            ),
        );

        /*
         * Calculate the badge after the database notification
         * has been created so the push contains the current
         * unread notification count.
         */
        $unreadCount =
            $user
                ->unreadNotifications()
                ->count();

        /*
         * Send the same event as a Web Push notification.
         *
         * Push delivery is intentionally non-fatal. A temporary
         * Web Push failure must never break Helm Score analysis
         * or prevent the database notification from existing.
         */
        try {
            $this->webPushService
                ->sendToUser(
                    $user,
                    [
                        'title' =>
                            $data['title']
                            ?? 'Helmio',

                        'body' =>
                            $data['message']
                            ?? 'You have a new Helmio notification.',

                        'action_url' =>
                            $data['action_url']
                            ?? '/notifications',

                        'unread_count' =>
                            $unreadCount,

                        'event_key' =>
                            $data['event_key'],

                        'severity' =>
                            $data['severity']
                            ?? 'information',

                        'type' =>
                            $data['type']
                            ?? 'helmio_notification',

                        'helm_score' =>
                            $data['helm_score']
                            ?? null,
                    ],
                );
        } catch (Throwable $exception) {
            report(
                $exception,
            );
        }
    }
}