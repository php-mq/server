<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\EventHandlers\Maintenance;

use PHPMQ\Server\CliWriter;
use PHPMQ\Server\Commands\ClearScreenCommand;
use PHPMQ\Server\Commands\FlushAllQueuesCommand;
use PHPMQ\Server\Commands\FlushQueueCommand;
use PHPMQ\Server\Commands\HelpCommand;
use PHPMQ\Server\Commands\QuitRefreshCommand;
use PHPMQ\Server\Commands\SearchQueueCommand;
use PHPMQ\Server\Commands\ShowQueueCommand;
use PHPMQ\Server\Commands\StartMonitorCommand;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\EventHandlers\Maintenance\ClientInboundEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientRequestedClearScreen;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingAllQueues;
use PHPMQ\Server\Events\Maintenance\ClientRequestedFlushingQueue;
use PHPMQ\Server\Events\Maintenance\ClientRequestedHelp;
use PHPMQ\Server\Events\Maintenance\ClientRequestedOverviewMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueSearch;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQuittingRefresh;
use PHPMQ\Server\Events\Maintenance\ClientSentUnknownCommand;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ClientInboundEventHandlerTest
 * @package PHPMQ\Server\Tests\Unit\EventHandlers\Maintenance
 */
final class ClientInboundEventHandlerTest extends TestCase
{
	use SocketMocking;
	use StorageMockingSQLite;
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	/** @var TransfersData */
	private $clientStream;

	/** @var TransfersData */
	private $remoteStream;

	protected function setUp() : void
	{
		$this->setUpStorage();
		$this->setUpServerSocket();
		$serverStream       = new Stream( $this->serverSocket );
		$this->remoteStream = new Stream( $this->getRemoteClientSocket() );
		$this->clientStream = $serverStream->acceptConnection();
	}

	protected function tearDown() : void
	{
		$this->tearDownStorage();
		$this->remoteStream->close();
		$this->clientStream->close();
		$this->tearDownServerSocket();
	}

	/**
	 * @param string $helpString
	 * @param string $expectedResponse
	 *
	 * @dataProvider helpStringProvider
	 */
	public function testCanHandleClientRequestedHelp( string $helpString, string $expectedResponse ) : void
	{
		$helpCommand          = new HelpCommand( $helpString );
		$event                = new ClientRequestedHelp( $this->clientStream, $helpCommand );
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$response = $this->remoteStream->read( 1024 );

		$expectedOutput = $cliWriter->clearScreen( 'HELP' )
									->write( $expectedResponse )
									->getInteractiveOutput();

		$this->assertEquals( $expectedOutput, $response );
	}

	public function helpStringProvider() : array
	{
		$helpFileDir = dirname( __DIR__, 4 ) . '/docs/MaintenanceCommandHelp';

		return [
			[
				'helpString'       => '',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help.txt' ),
			],
			[
				'helpString'       => 'clear',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-clear.txt' ),
			],
			[
				'helpString'       => 'exit',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-exit.txt' ),
			],
			[
				'helpString'       => 'quit',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-quit.txt' ),
			],
			[
				'helpString'       => 'search',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-search.txt' ),
			],
			[
				'helpString'       => 'show',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-show.txt' ),
			],
			[
				'helpString'       => 'flush',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-flush.txt' ),
			],
			[
				'helpString'       => 'flushall',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-flushall.txt' ),
			],
			[
				'helpString'       => 'monitor',
				'expectedResponse' => file_get_contents( $helpFileDir . '/help-monitor.txt' ),
			],
		];
	}

	public function testCanHandleUnknownCommandRequestedForHelp() : void
	{
		$helpCommand          = new HelpCommand( 'Unit-Test' );
		$event                = new ClientRequestedHelp( $this->clientStream, $helpCommand );
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$response = $this->remoteStream->read( 1024 );

		$expectedOutput = $cliWriter->clearScreen( 'HELP' )
									->writeLn( '' )
									->writeLn(
										'Help for unknown command "Unit-Test" requested.',
										$helpCommand->getCommandName()
									)
									->writeLn( '' )
									->writeFileContent(
										dirname( __DIR__, 4 ) . '/docs/MaintenanceCommandHelp/help.txt'
									)
									->getInteractiveOutput();

		$this->assertEquals( $expectedOutput, $response );
	}

	public function testCanHandleClientSentUnknownCommand() : void
	{
		$event                = new ClientSentUnknownCommand( $this->clientStream, 'Unit-Test' );
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$expectedOutput = $cliWriter->clearScreen( 'HELP' )
									->writeLn(
										'<bg:red>ERROR:<:bg> Unknown command "Unit-Test"',
										$event->getUnknownCommandString()
									)
									->writeLn( '' )
									->writeFileContent(
										dirname( __DIR__, 4 ) . '/docs/MaintenanceCommandHelp/help.txt'
									)
									->getInteractiveOutput();

		$response = $this->remoteStream->read( 1024 );

		$this->assertEquals( $expectedOutput, $response );
	}

	public function testCanHandleClientRequestedOverviewMonitor() : void
	{
		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'addWriteStream' )->with( $this->clientStream );

		$command = new StartMonitorCommand();

		/** @var TracksStreams $loop */
		$event                = new ClientRequestedOverviewMonitor( $this->clientStream, $loop, $command );
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$this->assertSame( $command, $event->getStartMonitorCommand() );
	}

	public function testCanHandleClientRequestedQueueMonitor() : void
	{
		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'addWriteStream' )->with( $this->clientStream );

		$showQueueCommand = new ShowQueueCommand( $this->getQueueName( 'Test-Queue' ) );

		/** @var TracksStreams $loop */
		$event                = new ClientRequestedQueueMonitor(
			$this->clientStream,
			$loop,
			$showQueueCommand
		);
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );
	}

	public function testCanHandleClientRequestedQuittingRefresh() : void
	{
		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'removeWriteStream' )->with( $this->clientStream );

		$command = new QuitRefreshCommand();

		/** @var TracksStreams $loop */
		$event                = new ClientRequestedQuittingRefresh( $this->clientStream, $loop, $command );
		$cliWriter            = new CliWriter();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$logger               = new NullLogger();
		$handler              = new ClientInboundEventHandler( $this->storage, $cliWriter, $serverMonitoringInfo );

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$this->assertSame( $command, $event->getQuitRefreshCommand() );
	}

	public function testCanHandleClientRequestedFlushingQueue() : void
	{
		$queueName = $this->getQueueName( 'Test-Queue' );
		$command   = new FlushQueueCommand( $queueName );
		$event     = new ClientRequestedFlushingQueue( $this->clientStream, $command );
		$storage   = $this->getMockBuilder( StoresMessages::class )->getMockForAbstractClass();
		$storage->expects( $this->once() )->method( 'flushQueue' )->with( $queueName );

		$serverMonitoringInfo = new ServerMonitoringInfo();

		$cliWriter = new CliWriter();
		$logger    = new NullLogger();

		/** @var StoresMessages $storage */
		$handler = new ClientInboundEventHandler(
			$storage,
			$cliWriter,
			$serverMonitoringInfo
		);

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );
	}

	public function testCanHandleClientRequestedFlushingAllQueues() : void
	{
		$command = new FlushAllQueuesCommand();
		$event   = new ClientRequestedFlushingAllQueues( $this->clientStream, $command );
		$storage = $this->getMockBuilder( StoresMessages::class )->getMockForAbstractClass();
		$storage->expects( $this->once() )->method( 'flushAllQueues' );

		$serverMonitoringInfo = new ServerMonitoringInfo();

		$cliWriter = new CliWriter();
		$logger    = new NullLogger();

		/** @var StoresMessages $storage */
		$handler = new ClientInboundEventHandler(
			$storage,
			$cliWriter,
			$serverMonitoringInfo
		);

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$this->assertSame( $command, $event->getFlushAllQueuesCommand() );
	}

	public function testCanHandleClientRequestedClearScreen() : void
	{
		$command              = new ClearScreenCommand();
		$event                = new ClientRequestedClearScreen( $this->clientStream, $command );
		$cliWriter            = new CliWriter();
		$logger               = new NullLogger();
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$handler = new ClientInboundEventHandler(
			$this->storage,
			$cliWriter,
			$serverMonitoringInfo
		);

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$response       = $this->remoteStream->read( 1024 );
		$expectedOutput = $cliWriter->clearScreen( 'Welcome!' )->getInteractiveOutput();

		$this->assertEquals( $expectedOutput, $response );
		$this->assertSame( $command, $event->getClearScreenCommand() );
	}

	/**
	 * @param string $searchTerm
	 *
	 * @dataProvider queueSearchProvider
	 */
	public function testCanHandleClientRequestedQueueSearch( string $searchTerm, string $expectedOutputRegExp ) : void
	{
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$command              = new SearchQueueCommand( $searchTerm );
		$event                = new ClientRequestedQueueSearch( $this->clientStream, $command );
		$cliWriter            = new CliWriter();
		$logger               = new NullLogger();
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$serverMonitoringInfo->addMessage(
			$queueName,
			new Message( $this->getMessageId( 'Unit-Test-ID' ), 'Unit-Test' )
		);

		/** @var CollectsServerMonitoringInfo $serverMonitoringInfo */
		$handler = new ClientInboundEventHandler(
			$this->storage,
			$cliWriter,
			$serverMonitoringInfo
		);

		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$response = $this->remoteStream->read( 1024 );

		$this->assertRegExp( $expectedOutputRegExp, $response );
	}

	public function queueSearchProvider() : array
	{
		return [
			[
				'searchTerm'           => '*est-Q*',
				'expectedOutputRegExp' => '#Test\-Queue#',
			],
			[
				'searchTerm'           => 'Test-Queue',
				'expectedOutputRegExp' => '#Test\-Queue#',
			],
			[
				'searchTerm'           => 'test*',
				'expectedOutputRegExp' => '#Test\-Queue#',
			],
			[
				'searchTerm'           => '*queue',
				'expectedOutputRegExp' => '#Test\-Queue#',
			],
			[
				'searchTerm'           => 'something',
				'expectedOutputRegExp' => '#^No queues found matching: "something"#',
			],
		];
	}
}
