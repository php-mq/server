<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\EventHandlers\MessageQueue\ClientConnectionEventHandler;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ClientConnectionEventHandlerTest
 * @package PHPMQ\Server\Tests\Unit\EventHandlers\MessageQueue
 */
final class ClientConnectionEventHandlerTest extends TestCase
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
	 * @param string $eventClass
	 *
	 * @dataProvider eventProvider
	 */
	public function testAcceptsEvents( string $eventClass ) : void
	{
		$handler = new ClientConnectionEventHandler(
			$this->storage,
			new ConsumptionPool(),
			new ServerMonitoringInfo()
		);

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
		$event                = new ClientConnected( $this->clientStream );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$handler              = new ClientConnectionEventHandler(
			$this->storage,
			new ConsumptionPool(),
			$serverMonitoringInfo
		);
		$handler->setLogger( new NullLogger() );

		$this->assertSame( 0, $serverMonitoringInfo->getConnectedClientsCount() );

		$handler->notify( $event );

		$this->assertSame( 1, $serverMonitoringInfo->getConnectedClientsCount() );
	}

	public function testCanHandleClientDisconnectedEvent() : void
	{
		$connectedEvent       = new ClientConnected( $this->clientStream );
		$disconnectedEvent    = new ClientDisconnected( $this->clientStream );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$handler              = new ClientConnectionEventHandler(
			$this->storage,
			new ConsumptionPool(),
			$serverMonitoringInfo
		);
		$handler->setLogger( new NullLogger() );

		$this->assertSame( 0, $serverMonitoringInfo->getConnectedClientsCount() );

		$handler->notify( $connectedEvent );

		$this->assertSame( 1, $serverMonitoringInfo->getConnectedClientsCount() );

		$handler->notify( $disconnectedEvent );

		$this->assertSame( 0, $serverMonitoringInfo->getConnectedClientsCount() );
	}

	public function testMessagesAreMarkedUndispatchedIfClientDisconnected() : void
	{
		$connectedEvent       = new ClientConnected( $this->clientStream );
		$disconnectedEvent    = new ClientDisconnected( $this->clientStream );
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$consumptionPool      = new ConsumptionPool();
		$handler              = new ClientConnectionEventHandler(
			$this->storage,
			$consumptionPool,
			$serverMonitoringInfo
		);

		$queueName = $this->getQueueName( 'Unit-Test-Queue' );
		$message   = new Message( $this->getMessageId( 'Unit-Test-ID' ), 'Unit-Test' );
		$this->storage->enqueue( $queueName, $message );
		$this->storage->markAsDispached( $queueName, $message->getMessageId() );
		$serverMonitoringInfo->addMessage( $queueName, $message );
		$serverMonitoringInfo->markMessageAsDispatched( $queueName, $message->getMessageId() );
		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$consumptionInfo->addMessageId( $message->getMessageId() );
		$consumptionPool->setConsumptionInfo( $this->clientStream->getStreamId(), $consumptionInfo );

		$handler->setLogger( new NullLogger() );

		$this->assertSame( 0, $serverMonitoringInfo->getConnectedClientsCount() );
		$this->assertCount( 1, $consumptionInfo->getMessageIds() );
		$this->assertCount( 0, $this->storage->getUndispatched( $queueName, 5 ) );

		$handler->notify( $connectedEvent );

		$this->assertSame( 1, $serverMonitoringInfo->getConnectedClientsCount() );

		$handler->notify( $disconnectedEvent );

		$this->assertSame( 0, $serverMonitoringInfo->getConnectedClientsCount() );
		$this->assertNotSame(
			$consumptionInfo,
			$consumptionPool->getConsumptionInfo( $this->clientStream->getStreamId() )
		);
		$this->assertCount( 1, $this->storage->getUndispatched( $queueName, 5 ) );
	}
}
