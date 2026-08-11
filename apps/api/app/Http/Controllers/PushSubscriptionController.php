<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(
        Request $request,
    ): JsonResponse {
        $validated = $request->validate([
            'endpoint' => [
                'required',
                'string',
            ],

            'keys.p256dh' => [
                'required',
                'string',
            ],

            'keys.auth' => [
                'required',
                'string',
            ],

            'content_encoding' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $endpointHash =
            hash(
                'sha256',
                $validated['endpoint'],
            );

        $subscription =
            PushSubscription::updateOrCreate(
                [
                    'user_id' =>
                        $request->user()->id,

                    'endpoint_hash' =>
                        $endpointHash,
                ],
                [
                    'endpoint' =>
                        $validated['endpoint'],

                    'public_key' =>
                        $validated['keys']['p256dh'],

                    'auth_token' =>
                        $validated['keys']['auth'],

                    'content_encoding' =>
                        $validated[
                            'content_encoding'
                        ] ?? 'aes128gcm',

                    'user_agent' =>
                        $request->userAgent(),
                ],
            );

        return response()->json([
            'status' =>
                'subscribed',

            'subscription_id' =>
                $subscription->id,
        ]);
    }

    public function publicKey(): JsonResponse
    {
        return response()->json([
            'public_key' =>
                config(
                    'webpush.vapid.public_key',
                ),
        ]);
    }

    public function destroy(
        Request $request,
    ): JsonResponse {
        $validated = $request->validate([
            'endpoint' => [
                'required',
                'string',
            ],
        ]);

        $endpointHash =
            hash(
                'sha256',
                $validated['endpoint'],
            );

        PushSubscription::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->where(
                'endpoint_hash',
                $endpointHash,
            )
            ->delete();

        return response()->json([
            'status' =>
                'unsubscribed',
        ]);
    }
}