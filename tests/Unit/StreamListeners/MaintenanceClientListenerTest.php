<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\StreamListeners;

use PHPMQ\Server\Commands\CommandBuilder;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\EventBus;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Events\Maintenance\ClientRequestedClearScreen;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueSearch;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\StreamListeners\MaintenanceClientListener;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\EventHandlerMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MaintenanceClientListenerTest
 * @package PHPMQ\Server\Tests\Unit\StreamListeners
 */
final class MaintenanceClientListenerTest extends TestCase
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

	/**
	 * @param string $command
	 * @param string $expectedEventClass
	 *
	 * @dataProvider commandEventClassProvider
	 */
	public function testCanGetCommandPublished( string $command, string $expectedEventClass ) : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$listener = new MaintenanceClientListener( $eventBus, new CommandBuilder() );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		$remoteStream->write( $command );

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$this->expectOutputString( $expectedEventClass . "\n" );

		$clientStream->close();
		$remoteStream->close();
	}

	public function commandEventClassProvider() : array
	{
		return [
			[
				'command'            => 'help',
				'expectedEventClass' => ClientRequestedHelp::class,
			],
			[
				'command'            => 'help show',
				'expectedEventClass' => ClientRequestedHelp::class,
			],
			[
				'command'            => 'clear',
				'expectedEventClass' => ClientRequestedClearScreen::class,
			],
			[
				'command'            => 'q',
				'expectedEventClass' => ClientRequestedQuittingRefresh::class,
			],
			[
				'command'            => 'quit',
				'expectedEventClass' => ClientDisconnected::class,
			],
			[
				'command'            => 'exit',
				'expectedEventClass' => ClientDisconnected::class,
			],
			[
				'command'            => 'show "Test"',
				'expectedEventClass' => ClientRequestedQueueMonitor::class,
			],
			[
				'command'            => 'show',
				'expectedEventClass' => ClientRequestedQueueMonitor::class,
			],
			[
				'command'            => 'monitor',
				'expectedEventClass' => ClientRequestedOverviewMonitor::class,
			],
			[
				'command'            => 'search "*what*"',
				'expectedEventClass' => ClientRequestedQueueSearch::class,
			],
			[
				'command'            => 'search',
				'expectedEventClass' => ClientRequestedQueueSearch::class,
			],
			[
				'command'            => 'flush "QueueName"',
				'expectedEventClass' => ClientRequestedFlushingQueue::class,
			],
			[
				'command'            => 'flushall',
				'expectedEventClass' => ClientRequestedFlushingAllQueues::class,
			],
			[
				'command'            => '',
				'expectedEventClass' => ClientDisconnected::class,
			],
			[
				'command'            => 'unknown',
				'expectedEventClass' => ClientSentUnknownCommand::class,
			],
		];
	}
}
