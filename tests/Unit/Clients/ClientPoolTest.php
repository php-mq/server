<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\ClientPool;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Servers\Interfaces\CommunicatesWithServer;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientPoolTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Clients
 */
final class ClientPoolTest extends TestCase
{
	use SocketMocking;

	public function setUp(): void
	{
		$this->setUpServerSocket();
	}

	public function tearDown(): void
	{
		$this->tearDownServerSocket();
	}

	public function testCanAddClients(): void
	{
		$pool          = new ClientPool();
		$remoteClient1 = $this->getRemoteClientSocket();

		$pool->add( $this->getClientMock( $this->getServerClientSocket() ) );

		$remoteClient2 = $this->getRemoteClientSocket();

		$pool->add( $this->getClientMock( $this->getServerClientSocket() ) );

		$this->assertCount( 2, $pool->getShuffled() );

		$pool->shutDown();

		fclose( $remoteClient1 );
		fclose( $remoteClient2 );
	}

	private function getClientMock( $acceptedSocket ): CommunicatesWithServer
	{
		return new class($acceptedSocket) implements CommunicatesWithServer
		{
			private $socket;

			private $clientId;

			public function __construct( $acceptedSocket )
			{
				$this->socket   = $acceptedSocket;
				$this->clientId = new ClientId( bin2hex( random_bytes( 16 ) ) );
			}

			public function getClientId(): IdentifiesClient
			{
				return $this->clientId;
			}

			public function read( int $bytes ): string
			{
				return fread( $this->socket, $bytes );
			}

			public function hasUnreadData(): bool
			{
				return (stream_get_meta_data( $this->socket )['unread_bytes'] > 0);
			}

			public function write( string $data ): int
			{
				return fwrite( $this->socket, $data );
			}

			public function collectSocket( array &$sockets ): void
			{
				$sockets[ $this->clientId->toString() ] = $this->socket;
			}

			public function shutDown(): void
			{
				stream_socket_shutdown( $this->socket, STREAM_SHUT_RDWR );
				fclose( $this->socket );
			}
		};
	}

	public function testCanRemoveClient(): void
	{
		$pool = new ClientPool();

		$remoteClient1 = $this->getRemoteClientSocket();
		$client1       = $this->getClientMock( $this->getServerClientSocket() );
		$remoteClient2 = $this->getRemoteClientSocket();
		$client2       = $this->getClientMock( $this->getServerClientSocket() );

		$pool->add( $client1 );
		$pool->add( $client2 );

		$this->assertCount( 2, $pool->getShuffled() );

		$pool->remove( $client1 );

		$this->assertCount( 1, $pool->getShuffled() );
		$this->assertSame( $client2, $pool->getShuffled()[0] );

		$pool->remove( $client2 );

		$this->assertCount( 0, $pool->getShuffled() );

		$pool->shutDown();

		fclose( $remoteClient1 );
		fclose( $remoteClient2 );
	}

	public function testCanShutDownClients(): void
	{
		$pool = new ClientPool();

		$remoteClient1 = $this->getRemoteClientSocket();
		$client1       = $this->getClientMock( $this->getServerClientSocket() );
		$remoteClient2 = $this->getRemoteClientSocket();
		$client2       = $this->getClientMock( $this->getServerClientSocket() );

		$pool->add( $client1 );
		$pool->add( $client2 );

		$this->assertCount( 2, $pool->getShuffled() );

		$pool->shutDown();

		$this->assertCount( 0, $pool->getShuffled() );

		fclose( $remoteClient1 );
		fclose( $remoteClient2 );
	}

	public function testCanGetActiveSockets(): void
	{
		$pool          = new ClientPool();
		$remoteClient1 = $this->getRemoteClientSocket();
		$serverClient1 = $this->getServerClientSocket();
		$client1       = $this->getClientMock( $serverClient1 );

		$remoteClient2 = $this->getRemoteClientSocket();
		$serverClient2 = $this->getServerClientSocket();
		$client2       = $this->getClientMock( $serverClient2 );

		$this->assertCount( 0, $pool->getActive() );

		$pool->add( $client1 );
		$pool->add( $client2 );

		$this->assertCount( 0, $pool->getActive() );

		fwrite( $remoteClient1, 'Unit-Test' );
		fwrite( $remoteClient2, 'Unit-Test' );

		$this->assertCount( 2, $pool->getActive() );

		fclose( $remoteClient1 );
		fclose( $remoteClient2 );
	}
}
