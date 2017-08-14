<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\EventHandlers\Maintenance;

use PHPMQ\Server\CliWriter;
use PHPMQ\Server\EventHandlers\Maintenance\ClientConnectionEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientConnected;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Loggers\AbstractLogger;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Stream\Interfaces\TransfersData;
use PHPMQ\Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ClientConnectionEventHandlerTest
 * @package PHPMQ\Server\Tests\Unit\EventHandlers\Maintenance
 */
final class ClientConnectionEventHandlerTest extends TestCase
{
	use SocketMocking;

	/** @var TransfersData */
	private $clientStream;

	/** @var TransfersData */
	private $remoteStream;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
		$serverStream       = new Stream( $this->serverSocket );
		$this->remoteStream = new Stream( $this->getRemoteClientSocket() );
		$this->clientStream = $serverStream->acceptConnection();
	}

	protected function tearDown() : void
	{
		$this->remoteStream->close();
		$this->clientStream->close();
		$this->tearDownServerSocket();
	}

	/**
	 * @param string $eventClass
	 *
	 * @dataProvider eventProvider
	 */
	public function testAcceptsEvents( string $eventClass ) : void
	{
		$handler = new ClientConnectionEventHandler( new CliWriter( '1.2.3' ) );

		$event = new $eventClass( $this->clientStream );

		$this->assertTrue( $handler->acceptsEvent( $event ) );
	}

	public function eventProvider() : array
	{
		return [
			[
				'eventClass' => ClientConnected::class,
			],
			[
				'eventClass' => ClientDisconnected::class,
			],
		];
	}

	public function testCanHandleClientConnectedEvent() : void
	{
		$event   = new ClientConnected( $this->clientStream );
		$handler = new ClientConnectionEventHandler( new CliWriter( '1.2.3' ) );
		$handler->setLogger( new NullLogger() );

		$handler->notify( $event );

		$this->assertRegExp( '#phpmq#', $this->remoteStream->read( 1024 ) );
	}

	public function testCanHandleClientDisconnectedEvent() : void
	{
		$disconnectedEvent = new ClientDisconnected( $this->clientStream );
		$handler           = new ClientConnectionEventHandler( new CliWriter( '1.2.3' ) );
		$logger            = new class extends AbstractLogger
		{
			public function log( $level, $message, array $context = [] ) : void
			{
				echo $level . ' - ' . $message;
			}
		};
		$handler->setLogger( $logger );

		$handler->notify( $disconnectedEvent );

		$this->expectOutputString( 'debug - Maintenance client disconnected: ' . $this->clientStream->getStreamId() );
	}
}
