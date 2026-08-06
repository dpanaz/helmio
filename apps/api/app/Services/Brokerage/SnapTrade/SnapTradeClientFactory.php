<?php

namespace App\Services\Brokerage\SnapTrade;

use RuntimeException;
use SnapTrade\Client;

class SnapTradeClientFactory
{
    public function make(): Client
    {
        $clientId = (string) config(
            'services.snaptrade.client_id'
        );

        $consumerKey = (string) config(
            'services.snaptrade.consumer_key'
        );

        if (
            $clientId === ''
            || $consumerKey === ''
        ) {
            throw new RuntimeException(
                'SnapTrade credentials are not configured.'
            );
        }

        return new Client(
            clientId:
                $clientId,

            consumerKey:
                $consumerKey,
        );
    }
}