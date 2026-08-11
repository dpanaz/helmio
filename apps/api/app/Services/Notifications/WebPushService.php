<?php

namespace App\Services\Notifications;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function sendToUser(
        User $user,
        array $payload,
    ): array {
        $auth = [
            'VAPID' => [
                'subject' =>
                    config(
                        'webpush.vapid.subject',
                    ),

                'publicKey' =>
                    config(
                        'webpush.vapid.public_key',
                    ),

                'privateKey' =>
                    config(
                        'webpush.vapid.private_key',
                    ),
            ],
        ];

        $webPush =
            new WebPush(
                $auth,
            );

        $results = [];

        $subscriptions =
            PushSubscription::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->get();

        foreach ($subscriptions as $storedSubscription) {
            $subscription =
                Subscription::create([
                    'endpoint' =>
                        $storedSubscription->endpoint,

                    'publicKey' =>
                        $storedSubscription->public_key,

                    'authToken' =>
                        $storedSubscription->auth_token,

                    'contentEncoding' =>
                        $storedSubscription->content_encoding,
                ]);

            $report =
                $webPush->sendOneNotification(
                    $subscription,
                    json_encode(
                        $payload,
                        JSON_THROW_ON_ERROR,
                    ),
                );

            $results[] = [
                'subscription_id' =>
                    $storedSubscription->id,

                'success' =>
                    $report->isSuccess(),

                'reason' =>
                    $report->getReason(),
            ];

            /*
             * Remove expired/invalid browser subscriptions.
             */
            if ($report->isSubscriptionExpired()) {
                $storedSubscription->delete();
            }
        }

        return $results;
    }
}