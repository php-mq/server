<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Fixtures\Traits;

/**
 * Trait SocketMocking
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Fixtures\Traits
 */
trait SocketMocking
{
	/** @var resource */
	private $serverSocket;

	public function setUpServerSocket(): void
	{
		$socketAddress = $this->getSocketAddress();

		$this->serverSocket = stream_socket_server( $socketAddress );
		stream_set_blocking( $this->serverSocket, false );
	}

	private function getSocketAddress(): string
	{
		return 'tcp://127.0.0.1:9005';
	}

	public function getRemoteClientSocket()
	{
		$socket = stream_socket_client( $this->getSocketAddress() );
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
