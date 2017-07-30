<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Endpoint;

use PHPMQ\Server\Endpoint\Endpoint;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Servers\Interfaces\EstablishesStream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class EndpointTest
 * @package PHPMQ\Server\Tests\Unit\Endpoint
 */
final class EndpointTest extends TestCase
{
	use SocketMocking;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
	}

	protected function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testCanAddServers() : void
	{
		$logger = new NullLogger();
		$loop   = $this->getMockBuilder( TracksStreams::class )
		               ->setMethods( [ 'addReadStream' ] )
		               ->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'addReadStream' );

		$listener = $this->getMockBuilder( ListensForStreamActivity::class )->getMockForAbstractClass();

		/** @var TracksStreams $loop */
		$endpoint = new Endpoint( $logger, $loop );

		$server = $this->getMockBuilder( EstablishesStream::class )
		               ->setMethods( [ 'getStream' ] )
		               ->getMockForAbstractClass();
		$server->expects( $this->once() )->method( 'getStream' )->willReturn( new Stream( $this->serverSocket ) );

		/** @var EstablishesStream $server */
		/** @var ListensForStreamActivity $listener */
		$endpoint->addServer( $server, $listener );
	}

	public function testCanRun() : void
	{
		$logger = new NullLogger();
		$loop   = $this->getMockBuilder( TracksStreams::class )
		               ->setMethods( [ 'start' ] )
		               ->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'start' );

		/** @var TracksStreams $loop */
		$endpoint = new Endpoint( $logger, $loop );
		$endpoint->run();
	}
}
