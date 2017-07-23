<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Servers;

use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Servers\Interfaces\IdentifiesSocketAddress;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Servers\Types\NetworkSocket;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;

/**
 * Class ServerSocketTest
 * @package PHPMQ\Server\Tests\Unit\Servers
 */
final class ServerSocketTest extends TestCase
{
	use SocketMocking;

	public function testCanGetStream() : void
	{
		$networkSocket = new NetworkSocket( self::$SERVER_HOST, self::$SERVER_PORT );
		$serverSocket  = new ServerSocket( $networkSocket );

		$stream = $serverSocket->getStream();

		$this->assertInstanceOf( TransfersData::class, $stream );
	}

	/**
	 * @expectedException \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function testInvalidSocketAddressThrowsException() : void
	{
		$socketAddress = $this->getMockBuilder( IdentifiesSocketAddress::class )->getMockForAbstractClass();
		$socketAddress->expects( $this->any() )->method( 'getSocketAddress' )->willReturn( 'php://stdin' );

		/** @var IdentifiesSocketAddress $socketAddress */
		$serverSocket = new ServerSocket( $socketAddress );

		$serverSocket->getStream();
	}
}
