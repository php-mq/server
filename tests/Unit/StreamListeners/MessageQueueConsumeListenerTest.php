<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\StreamListeners;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\ServerMonitoringInfo;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\StreamListeners\MessageQueueConsumeListener;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMockingSQLite;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StreamIdentifierMocking;
use PHPMQ\Server\Types\Message;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageQueueConsumeListenerTest
 * @package PHPMQ\Server\Tests\Unit\StreamListeners
 */
final class MessageQueueConsumeListenerTest extends TestCase
{
	use StorageMockingSQLite;
	use StreamIdentifierMocking;
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;
	use SocketMocking;

	public function setUp() : void
	{
		$this->setUpStorage();
		$this->setUpServerSocket();
	}

	public function tearDown() : void
	{
		$this->tearDownServerSocket();
		$this->tearDownStorage();
	}

	public function testCanSendMessagesToConsumer() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$queueName = $this->getQueueName( 'Test-Queue' );

		$this->enqueueMessages( $queueName, 2, $serverMonitoringInfo );

		$listener = new MessageQueueConsumeListener( $this->storage, $consumptionPool, $serverMonitoringInfo );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$consumptionPool->setConsumptionInfo(
			$clientStream->getStreamId(),
			new ConsumptionInfo( $this->getQueueName( 'Test-Queue' ), 1 )
		);

		$consumptionInfo = $consumptionPool->getConsumptionInfo( $clientStream->getStreamId() );

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		# Send first message to consumer

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$expectedMessage = new MessageE2C(
			$this->getMessageId( 'Unit-Test-ID-1' ),
			$this->getQueueName( 'Test-Queue' ),
			'Unit-Test-1'
		);

		$readString = $remoteStream->read( 1024 );

		$this->assertSame( $expectedMessage->toString(), $readString );

		$consumptionInfo->removeMessageId( $expectedMessage->getMessageId() );

		# Send second message to consumer

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$expectedMessage = new MessageE2C(
			$this->getMessageId( 'Unit-Test-ID-2' ),
			$this->getQueueName( 'Test-Queue' ),
			'Unit-Test-2'
		);

		$readString = $remoteStream->read( 1024 );

		$this->assertSame( $expectedMessage->toString(), $readString );

		# Consumer cannot consume more than 1 message

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$readString = $remoteStream->read( 1024 );

		$this->assertSame( '', $readString );

		$consumptionInfo->removeMessageId( $expectedMessage->getMessageId() );

		# No more messages to consume

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$readString = $remoteStream->read( 1024 );

		$this->assertSame( '', $readString );

		$clientStream->close();
		$remoteStream->close();
	}

	private function enqueueMessages(
		IdentifiesQueue $queueName,
		int $countMessages,
		CollectsServerMonitoringInfo $serverMonitoringInfo
	) : void
	{
		for ( $i = 0; $i < $countMessages; $i++ )
		{
			$nr = $i + 1;

			$message = new Message( $this->getMessageId( "Unit-Test-ID-{$nr}" ), "Unit-Test-{$nr}" );

			$this->storage->enqueue( $queueName, $message );
			$serverMonitoringInfo->addMessage( $queueName, $message );
		}
	}

	public function testWriteTimeOutRemovesWriteStreamFromLoop() : void
	{
		$logger               = new NullLogger();
		$consumptionPool      = new ConsumptionPool();
		$serverMonitoringInfo = new ServerMonitoringInfo();

		$queueName = $this->getQueueName( 'Test-Queue' );

		$this->enqueueMessages( $queueName, 1, $serverMonitoringInfo );

		$listener = new MessageQueueConsumeListener( $this->storage, $consumptionPool, $serverMonitoringInfo );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$consumptionPool->setConsumptionInfo(
			$clientStream->getStreamId(),
			new ConsumptionInfo( $this->getQueueName( 'Test-Queue' ), 1 )
		);

		$loop = $this->getMockBuilder( TracksStreams::class )
		             ->setMethods( [ 'removeWriteStream' ] )
		             ->getMockForAbstractClass();
		$loop->expects( $this->once() )->method( 'removeWriteStream' );

		$clientStream->shutDown();

		# Try to send message to consumer

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$clientStream->close();
		$remoteStream->close();
	}
}
