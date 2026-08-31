<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if (
            ! $user ||
            ! Hash::check(
                $credentials['password'],
                $user->password,
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'The provided credentials are incorrect.',
                ],
            ]);
        }

        /*
         * Optional but recommended:
         * remove old token from the same device name.
         */
        $deviceName =
            $credentials['device_name']
            ?? 'helmio-mobile';

        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        $token = $user
            ->createToken($deviceName)
            ->plainTextToken;

        return response()->json([
            'token' => $token,

            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'subscription' => $this->subscriptionData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'subscription' => $this->subscriptionData($user),
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        return response()->json([
            'subscription' => $this->subscriptionData(
                $request->user(),
            ),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Signed out successfully.',
        ]);
    }

    private function subscriptionData(User $user): array
        {
            /*
            * Demo accounts should always be able to access
            * the Helmio dashboard without a Stripe subscription.
            */
            $isDemo =
                strtolower((string) $user->email) === 'demo@myhelmio.com'
                || strtolower((string) $user->name) === 'demo';

            if ($isDemo) {
                return [
                    'active' => true,
                    'demo' => true,
                ];
            }

            $active = method_exists($user, 'subscribed')
                ? $user->subscribed('default')
                : false;

            return [
                'active' => $active,
                'demo' => false,
            ];
        }
}