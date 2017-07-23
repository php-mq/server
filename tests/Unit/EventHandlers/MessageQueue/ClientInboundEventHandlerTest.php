<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\EventHandlers\MessageQueue\ClientInboundEventHandler;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Monitoring\Types\QueueInfo;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ClientInboundEventHandlerTest
 * @package PHPMQ\Server\Tests\Unit\EventHandlers\MessageQueue
 */
final class ClientInboundEventHandlerTest extends TestCase
{
	use SocketMocking;
	use StorageMockingSQLite;
	use QueueIdentifierMocking;

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

	public function testCanHandleClientSentMessageC2E() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$message              = new MessageC2E( $queueName, 'Unit-Test' );

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		/** @var TracksStreams $loop */
		$event = new ClientSentMessageC2E( $message, $this->clientStream, $loop );

		$handler = new ClientInboundEventHandler(
			$this->storage,
			$consumptionPool,
			$serverMonitoringInfo
		);
		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$this->assertSame( 1, $serverMonitoringInfo->getQueueCount() );
		$this->assertSame( 1, $serverMonitoringInfo->getQueueInfo( $queueName )->getMessageCount() );
		$this->assertCount( 1, iterator_to_array( $this->storage->getUndispatched( $queueName, 5 ) ) );
		$this->assertSame( $this->clientStream, $event->getStream() );
		$this->assertSame( $loop, $event->getLoop() );
	}

	public function testCanHandleClientSentConsumeRequest() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$serverMonitoringInfo = new ServerMonitoringInfo();
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$consumeRequest       = new ConsumeRequest( $queueName, 5 );

		$loop = $this->getMockBuilder( TracksStreams::class )
					 ->setMethods( ['addWriteStream'] )
					 ->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'addWriteStream' );

		/** @var TracksStreams $loop */
		$event = new ClientSentConsumeResquest( $consumeRequest, $this->clientStream, $loop );

		$handler = new ClientInboundEventHandler(
			$this->storage,
			$consumptionPool,
			$serverMonitoringInfo
		);
		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$expetcedConsumptionInfo = new ConsumptionInfo( $queueName, 5 );

		$this->assertEquals(
			$expetcedConsumptionInfo,
			$consumptionPool->getConsumptionInfo( $this->clientStream->getStreamId() )
		);
	}

	public function testConsumptionInfoIsCleanedUpWhenClientSentNewConsumptionInfo() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$messageId = new MessageId( 'Unit-Test-ID' );
		$message   = new Message( $messageId, 'Unit-Test' );
		$this->storage->enqueue( $queueName, $message );
		$serverMonitoringInfo->addMessage( $queueName, $message );

		$this->storage->markAsDispached( $queueName, $message->getMessageId() );
		$serverMonitoringInfo->markMessageAsDispatched( $queueName, $message->getMessageId() );

		$consumptionInfo = new ConsumptionInfo( $queueName, 3 );
		$consumptionInfo->addMessageId( $messageId );
		$consumptionPool->setConsumptionInfo( $this->clientStream->getStreamId(), $consumptionInfo );

		$consumeRequest = new ConsumeRequest( $queueName, 5 );

		$loop = $this->getMockBuilder( TracksStreams::class )
					 ->setMethods( ['addWriteStream'] )
					 ->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'addWriteStream' );

		/** @var TracksStreams $loop */
		$event = new ClientSentConsumeResquest( $consumeRequest, $this->clientStream, $loop );

		$handler = new ClientInboundEventHandler(
			$this->storage,
			$consumptionPool,
			$serverMonitoringInfo
		);
		$handler->setLogger( $logger );

		$handler->notify( $event );

		$expetcedConsumptionInfo = new ConsumptionInfo( $queueName, 5 );

		$this->assertEquals(
			$expetcedConsumptionInfo,
			$consumptionPool->getConsumptionInfo( $this->clientStream->getStreamId() )
		);

		$undispatchedMessages = iterator_to_array( $this->storage->getUndispatched( $queueName ) );

		$this->assertEquals( $message, $undispatchedMessages[0] );

		$expectedQueueInfo = new QueueInfo(
			$queueName->toString(),
			[
				$messageId->toString() => [
					'messageId'  => $messageId->toString(),
					'dispatched' => false,
					'size'       => strlen( $message->getContent() ),
					'createdAt'  => $message->createdAt(),
				],
			]
		);

		$this->assertEquals( $expectedQueueInfo, $serverMonitoringInfo->getQueueInfo( $queueName ) );
	}

	public function testCanHandleAcknowledgement() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$queueName            = $this->getQueueName( 'Test-Queue' );
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$messageId = new MessageId( 'Unit-Test-ID' );
		$message   = new Message( $messageId, 'Unit-Test' );
		$this->storage->enqueue( $queueName, $message );
		$serverMonitoringInfo->addMessage( $queueName, $message );

		$this->storage->markAsDispached( $queueName, $message->getMessageId() );
		$serverMonitoringInfo->markMessageAsDispatched( $queueName, $message->getMessageId() );

		$consumptionInfo = new ConsumptionInfo( $queueName, 3 );
		$consumptionInfo->addMessageId( $messageId );
		$consumptionPool->setConsumptionInfo( $this->clientStream->getStreamId(), $consumptionInfo );

		$acknowledgement = new Acknowledgement( $queueName, $messageId );

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		/** @var TracksStreams $loop */
		$event = new ClientSentAcknowledgement( $acknowledgement, $this->clientStream, $loop );

		$handler = new ClientInboundEventHandler(
			$this->storage,
			$consumptionPool,
			$serverMonitoringInfo
		);
		$handler->setLogger( $logger );

		$this->assertTrue( $handler->acceptsEvent( $event ) );

		$handler->notify( $event );

		$this->assertCount( 0, $consumptionInfo->getMessageIds() );
		$this->assertSame( 0, $serverMonitoringInfo->getQueueCount() );
		$this->assertCount( 0, iterator_to_array( $this->storage->getUndispatched( $queueName ) ) );

		$this->assertSame( $this->clientStream, $event->getStream() );
		$this->assertSame( $loop, $event->getLoop() );
	}
}
