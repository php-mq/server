<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Servers;

use PHPMQ\Server\Endpoint\Interfaces\ListensForActivity;
use PHPMQ\Server\Servers\AbstractServer;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Servers\Interfaces\TracksClients;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class AbstractServerTest
 * @package PHPMQ\Server\Tests\Unit\Servers
 */
final class AbstractServerTest extends TestCase
{
	public function testCanStartServer() : void
	{
		$serverSocket = $this->getServerSocketMock();
		$clientPool   = $this->getClientPoolMock();
		$server       = $this->getTestServer( $serverSocket, $clientPool );
		$server->setLogger( new NullLogger() );

		$serverSocket->expects( $this->once() )->method( 'startListening' );

		$server->start();
	}

	private function getTestServer( $serverSocket, $clientPool ) : ListensForActivity
	{
		return new class($serverSocket, $clientPool) extends AbstractServer
		{
			public function getEvents() : \Generator
			{
				yield null;
			}
		};
	}

	private function getServerSocketMock() : \PHPUnit_Framework_MockObject_MockObject
	{
		$serverSocket = $this->getMockBuilder( EstablishesStream::class )->getMockForAbstractClass();

		return $serverSocket;
	}

	private function getClientPoolMock() : \PHPUnit_Framework_MockObject_MockObject
	{
		$clientPool = $this->getMockBuilder( TracksClients::class )->getMockForAbstractClass();

		return $clientPool;
	}

	public function testCanStopServer() : void
	{
		$serverSocket = $this->getServerSocketMock();
		$clientPool   = $this->getClientPoolMock();
		$server       = $this->getTestServer( $serverSocket, $clientPool );
		$server->setLogger( new NullLogger() );

		$serverSocket->expects( $this->once() )->method( 'endListening' );
		$clientPool->expects( $this->once() )->method( 'shutDown' );

		$server->stop();
	}
}
