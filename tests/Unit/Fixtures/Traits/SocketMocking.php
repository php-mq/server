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
	private $socketServer;

	/** @var resource */
	private $socketClient;

	public function setUpSockets() : void
	{
		$this->socketServer = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		@unlink( '/tmp/mock.sock' );
		socket_bind( $this->socketServer, '/tmp/mock.sock' );
		socket_listen( $this->socketServer, SOMAXCONN );

		$this->socketClient = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		socket_connect( $this->socketClient, '/tmp/mock.sock' );
	}

	public function tearDownSockets() : void
	{
		socket_shutdown( $this->socketServer );
		socket_shutdown( $this->socketClient );
		socket_close( $this->socketServer );
		socket_close( $this->socketClient );
	}
}
