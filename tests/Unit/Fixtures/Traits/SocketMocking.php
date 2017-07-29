<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

/**
 * Trait SocketMocking
 * @package PHPMQ\Server\Tests\Unit\Fixtures\Traits
 */
trait SocketMocking
{
	private static $SERVER_HOST = '127.0.0.1';

	private static $SERVER_PORT = 9005;

	/** @var resource */
	private $serverSocket;

	public function setUpServerSocket() : void
	{
		$socketAddress = $this->getSocketAddressString();

		$this->serverSocket = stream_socket_server( $socketAddress );
		stream_set_blocking( $this->serverSocket, false );
	}

	private function getSocketAddressString() : string
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

	public function tearDownServerSocket() : void
	{
		/** @noinspection UsageOfSilenceOperatorInspection */
		@stream_socket_shutdown( $this->serverSocket, STREAM_SHUT_RDWR );
		@fclose( $this->serverSocket );
	}
}
