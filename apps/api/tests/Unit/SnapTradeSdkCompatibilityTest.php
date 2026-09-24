<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SnapTrade\Api\AccountInformationApi;
use SnapTrade\Api\ConnectionsApi;
use SnapTrade\Model\BrokerageAuthorizationDataFreshnessMode;
use SnapTrade\ObjectSerializer;

class SnapTradeSdkCompatibilityTest extends TestCase
{
    public function test_connection_response_with_structured_freshness_mode(): void
    {
        $response = json_decode(
            '[{"id":"example-authorization","data_freshness_mode":{"institution":"delayed","snaptrade":"delayed"}}]',
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        $connections = ObjectSerializer::deserialize(
            $response,
            '\\SnapTrade\\Model\\BrokerageAuthorization[]',
            [],
        );

        self::assertInstanceOf(
            BrokerageAuthorizationDataFreshnessMode::class,
            $connections[0]->getDataFreshnessMode(),
        );
    }

    public function test_connection_operations_used_by_helmio_exist(): void
    {
        self::assertTrue(method_exists(AccountInformationApi::class, 'getAllAccountPositions'));
        self::assertTrue(method_exists(ConnectionsApi::class, 'deleteConnection'));
    }
}
