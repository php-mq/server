<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Clients;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 * @package hollodotme\PHPMQ\Tests\Unit\Clients
 */
final class ClientTest extends TestCase
{
	public function testClientIsNotDisconnectedAfterConstruction() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->getTestSocket() );

		$this->assertSame( $clientId, $client->getClientId() );
		$this->assertSame( (string)$clientId, $client->getClientId()->toString() );
		$this->assertFalse( $client->isDisconnected() );
	}

	private function getTestSocket()
	{
		return fopen( __DIR__ . '/../Fixtures/mock.sock', 'rb' );
	}

	public function testCanCollectSocket() : void
	{
		$clientId        = ClientId::generate();
		$socket          = $this->getTestSocket();
		$client          = new Client( $clientId, $socket );
		$expectedSockets = [
			$clientId->toString() => $socket,
		];

		$sockets = [];

		$client->collectSocket( $sockets );

		$this->assertSame( $expectedSockets, $sockets );
	}
}
