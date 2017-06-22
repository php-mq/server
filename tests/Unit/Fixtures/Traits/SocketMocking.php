<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Servers\Types\NetworkSocket;

/**
 * Trait SocketMocking
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Fixtures\Traits
 */
trait SocketMocking
{
	private static $SERVER_HOST = '127.0.0.1';

	private static $SERVER_PORT = 9005;

	/** @var resource */
	private $serverSocket;

	public function setUpServerSocket(): void
	{
		$socketAddress = $this->getSocketAddressString();

		$this->serverSocket = stream_socket_server( $socketAddress );
		stream_set_blocking( $this->serverSocket, false );
	}

	private function getSocketAddress(): IdentifiesSocketAddress
	{
		return new NetworkSocket( self::$SERVER_HOST, self::$SERVER_PORT );
	}

	private function getSocketAddressString(): string
	{
		return sprintf( 'tcp://%s:%d', self::$SERVER_HOST, self::$SERVER_PORT );
	}

	public function getRemoteClientSocket()
	{
		$socket = stream_socket_client( $this->getSocketAddressString() );
		stream_set_blocking( $socket, false );

		return $socket;
	}

	public function getServerClientSocket()
	{
		$socket = stream_socket_accept( $this->serverSocket, 5 );
		stream_set_blocking( $socket, false );

		return $socket;
	}

	public function tearDownServerSocket(): void
	{
		stream_socket_shutdown( $this->serverSocket, STREAM_SHUT_RDWR );
		fclose( $this->serverSocket );
	}
}
