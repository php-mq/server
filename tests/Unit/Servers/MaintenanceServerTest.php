<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Servers;

use PHPMQ\Server\Commands\Constants\Command;
use PHPMQ\Server\Events\Maintenance\ClientConnected;
use PHPMQ\Server\Events\Maintenance\ClientDisconnected;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Servers\MaintenanceServer;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MaintenanceServerTest
 * @package PHPMQ\Server\Tests\Unit\Servers
 */
final class MaintenanceServerTest extends TestCase
{
	use SocketMocking;

	public function testCanGetClientConnectionEvent() : void
	{
		$server = new MaintenanceServer( new ServerSocket( $this->getSocketAddress() ) );
		$server->setLogger( new NullLogger() );
		$server->start();

		$remoteClient = $this->getRemoteClientSocket();

		$events = iterator_to_array( $server->getEvents() );

		$this->assertInstanceOf( ClientConnected::class, $events[0] );

		fclose( $remoteClient );

		$events = iterator_to_array( $server->getEvents() );

		$this->assertInstanceOf( ClientDisconnected::class, $events[0] );

		$server->stop();
	}

	/**
	 * @param string $command
	 * @param string $expectedEventClass
	 *
	 * @dataProvider commandProvider
	 */
	public function testCanGetCommandEvents( string $command, string $expectedEventClass ) : void
	{
		$server = new MaintenanceServer( new ServerSocket( $this->getSocketAddress() ) );
		$server->setLogger( new NullLogger() );
		$server->start();

		$remoteClient = $this->getRemoteClientSocket();

		fwrite( $remoteClient, $command );

		$events = iterator_to_array( $server->getEvents() );

		$this->assertInstanceOf( $expectedEventClass, $events[0] );

		$events = iterator_to_array( $server->getEvents() );
		$this->assertCount( 0, $events );
		
		$server->stop();
		fclose( $remoteClient );
	}

	public function commandProvider() : array
	{
		return [
			[
				'command'            => Command::HELP,
				'expectedEventClass' => ClientRequestedHelp::class,
			],
			[
				'command'            => Command::START_MONITOR,
				'expectedEventClass' => ClientRequestedOverviewMonitor::class,
			],
			[
				'command'            => Command::SHOW_QUEUE,
				'expectedEventClass' => ClientRequestedQueueMonitor::class,
			],
			[
				'command'            => Command::QUIT_REFRESH,
				'expectedEventClass' => ClientRequestedQuittingRefresh::class,
			],
			[
				'command'            => Command::FLUSH_QUEUE,
				'expectedEventClass' => ClientRequestedFlushingQueue::class,
			],
			[
				'command'            => Command::FLUSH_ALL_QUEUES,
				'expectedEventClass' => ClientRequestedFlushingAllQueues::class,
			],
			[
				'command'            => Command::EXIT,
				'expectedEventClass' => ClientDisconnected::class,
			],
			[
				'command'            => Command::QUIT,
				'expectedEventClass' => ClientDisconnected::class,
			],
			[
				'command'            => 'unit-test',
				'expectedEventClass' => ClientSentUnknownCommand::class,
			],
		];
	}
}
