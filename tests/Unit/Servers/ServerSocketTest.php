<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Tests\Unit\Servers;

use PHPMQ\Server\Endpoint\Types\UnixDomainSocket;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;

final class ServerSocketTest extends TestCase
{
	use SocketMocking;

	public function testCanGetName(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );

		$expectedName = $this->getSocketAddressString();
		$this->assertSame( $expectedName, $server->getName() );
	}

	public function testCanGetNewClient(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );
		$server->startListening();

		$remoteClient = $this->getRemoteClientSocket();

		$serverClientInfo = $server->getNewClient();

		$this->assertNotNull( $serverClientInfo );

		$this->assertRegExp( '#' . self::$SERVER_HOST . '#', $serverClientInfo->getName() );
		$this->assertInternalType( 'resource', $serverClientInfo->getSocket() );

		fclose( $remoteClient );
		$server->endListening();
	}

	public function testTryingToGetANewClientWhenServerIsNotListeningReturnsNull(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );

		$this->assertNull( $server->getNewClient() );

		$server->endListening();
	}

	public function testTryingToGetANewClientWhenNoneConnectedReturnsNull(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );
		$server->startListening();

		$this->assertNull( $server->getNewClient() );

		$server->endListening();
	}

	/**
	 * @expectedException \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function testFailureOnEstablishingSocketThrowsException(): void
	{
		$server = new ServerSocket( new UnixDomainSocket( '/not/existing/path.sock' ) );
		$server->startListening();
	}

	public function testStartListeningMultipleTimesHasNoEffect(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );
		$server->startListening();
		$server->startListening();

		$this->assertTrue( true );

		$server->endListening();
	}

	public function testEndListeningMultipleTimesHasNoEffect(): void
	{
		$server = new ServerSocket( $this->getSocketAddress() );
		$server->startListening();

		$this->assertTrue( true );

		$server->endListening();
		$server->endListening();
		$server->endListening();
	}
}
