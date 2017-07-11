<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\EventBus;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\StreamListeners\MessageQueueServerListener;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\EventHandlerMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageQueueServerListenerTest
 * @package PHPMQ\Server\Tests\Unit\StreamListeners
 */
final class MessageQueueServerListenerTest extends TestCase
{
	use SocketMocking;
	use EventHandlerMocking;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
	}

	protected function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testCanAcceptClientConnection() : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$listener = new MessageQueueServerListener( $eventBus );
		$listener->setLogger( $logger );

		$stream = new Stream( $this->serverSocket );

		$loop = $this->getMockBuilder( TracksStreams::class )
		             ->setMethods( [ 'addReadStream' ] )
		             ->getMockForAbstractClass();

		$loop->expects( $this->once() )
		     ->method( 'addReadStream' );

		$remoteSocket = $this->getRemoteClientSocket();

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $stream, $loop );

		$this->expectOutputString( ClientConnected::class . "\n" );

		fclose( $remoteSocket );
	}

	public function testCanNotAcceptClientConnectionIfNoneConnected() : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$listener = new MessageQueueServerListener( $eventBus );
		$listener->setLogger( $logger );

		$stream = new Stream( $this->serverSocket );

		$loop = $this->getMockBuilder( TracksStreams::class )
		             ->setMethods( [ 'addReadStream' ] )
		             ->getMockForAbstractClass();

		$loop->expects( $this->never() )
		     ->method( 'addReadStream' );

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $stream, $loop );
	}
}
