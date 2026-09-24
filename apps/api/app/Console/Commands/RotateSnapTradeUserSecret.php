<?php

namespace App\Console\Commands;

use App\Models\BrokerageProviderUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SnapTrade\Client;
use Throwable;

class RotateSnapTradeUserSecret extends Command
{
    protected $signature = 'helmio:snaptrade-rotate-user-secret {userId : Helmio user ID} {--confirm-provider-user-id= : Exact SnapTrade user ID to authorize non-interactive rotation}';

    protected $description = 'Rotate one SnapTrade user secret and immediately save it encrypted';

    public function handle(Client $client): int
    {
        $userId = (int) $this->argument('userId');
        $providerUser = BrokerageProviderUser::query()
            ->where('user_id', $userId)
            ->where('provider', 'snaptrade')
            ->first();

        if ($providerUser === null) {
            $this->error('No SnapTrade user exists for that Helmio user ID.');
            return self::FAILURE;
        }

        $providerUserId = $providerUser->provider_user_id;
        $confirmedProviderUserId = $this->option('confirm-provider-user-id');

        if ($confirmedProviderUserId !== null) {
            if ($confirmedProviderUserId !== $providerUserId) {
                $this->error('Confirmed SnapTrade user ID does not match the stored connection.');
                return self::FAILURE;
            }
        } elseif (! $this->confirm("Rotate the secret for {$providerUserId}? This immediately invalidates the old secret.")) {
            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($client, $userId, $providerUserId): void {
                $record = BrokerageProviderUser::query()
                    ->where('user_id', $userId)
                    ->where('provider', 'snaptrade')
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($record->provider_user_id !== $providerUserId) {
                    throw new RuntimeException('SnapTrade user changed before rotation.');
                }

                $response = $client->authentication->resetSnapTradeUserSecret(
                    user_id: $providerUserId,
                    user_secret: $record->provider_user_secret,
                );
                $newSecret = $response->getUserSecret();

                if (! is_string($newSecret) || $newSecret === '') {
                    throw new RuntimeException('SnapTrade did not return a new user secret.');
                }

                $record->provider_user_secret = $newSecret;
                $record->saveOrFail();

                if ($record->fresh()->provider_user_secret !== $newSecret) {
                    throw new RuntimeException('The new secret could not be verified in Helmio.');
                }
            }, attempts: 1);
        } catch (Throwable $exception) {
            // SDK exception messages can include the old secret in a request URL.
            // Do not report or print the exception or its stack trace.
            $this->error('Rotation could not be confirmed. Stop and investigate securely before attempting another rotation.');
            return self::FAILURE;
        }

        $this->info("Rotated and stored the secret for {$providerUserId}.");
        return self::SUCCESS;
    }
}
