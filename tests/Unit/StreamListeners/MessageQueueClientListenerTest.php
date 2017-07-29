<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\StreamListeners;

use PHPMQ\Protocol\Interfaces\BuildsMessages;
use PHPMQ\Protocol\Interfaces\DefinesMessage;
use PHPMQ\Protocol\Interfaces\IdentifiesMessageType;
use PHPMQ\Protocol\Interfaces\ProvidesMessageData;
use PHPMQ\Protocol\Messages\Acknowledgement;
use PHPMQ\Protocol\Messages\ConsumeRequest;
use PHPMQ\Protocol\Messages\MessageClientToServer;
use PHPMQ\Protocol\Types\MessageType;
use PHPMQ\Server\Builders\MessageBuilder;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\EventBus;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessage;
use PHPMQ\Server\StreamListeners\MessageQueueClientListener;
use PHPMQ\Server\Streams\Stream;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\EventHandlerMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\MessageIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\QueueIdentifierMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Traits\StringRepresenting;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageQueueClientListenerTest
 * @package PHPMQ\Server\Tests\Unit\StreamListeners
 */
final class MessageQueueClientListenerTest extends TestCase
{
	use SocketMocking;
	use EventHandlerMocking;
	use QueueIdentifierMocking;
	use MessageIdentifierMocking;

	protected function setUp() : void
	{
		$this->setUpServerSocket();
	}

	protected function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	/**
	 * @param ProvidesMessageData $message
	 * @param string              $expectedEventClass
	 *
	 * @dataProvider messageEventClassProvider
	 */
	public function testCanGetMessageEventPublished( ProvidesMessageData $message, string $expectedEventClass ) : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$listener = new MessageQueueClientListener( $eventBus, new MessageBuilder() );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		$remoteStream->write( $message->toString() );

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$this->expectOutputString( $expectedEventClass . "\n" );

		$clientStream->close();
		$remoteStream->close();
	}

	public function messageEventClassProvider() : array
	{
		return [
			[
				'message'            => new MessageClientToServer( $this->getQueueName( 'Test-Queue' ), 'Unit-Test' ),
				'expectedEventClass' => ClientSentMessage::class,
			],
			[
				'message'            => new ConsumeRequest( $this->getQueueName( 'Test-Queue' ), 5 ),
				'expectedEventClass' => ClientSentConsumeResquest::class,
			],
			[
				'message'            => new Acknowledgement(
					$this->getQueueName( 'Test-Queue' ),
					$this->getMessageId( 'Unit-Test-ID' )
				),
				'expectedEventClass' => ClientSentAcknowledgement::class,
			],
		];
	}

	public function testCanGetClientDisconnectEventPublished() : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$listener = new MessageQueueClientListener( $eventBus, new MessageBuilder() );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$this->expectOutputString( ClientDisconnected::class . "\n" );

		$clientStream->close();
		$remoteStream->close();
	}

	/**
	 * @expectedException \PHPMQ\Server\StreamListeners\Exceptions\InvalidMessageTypeReceivedException
	 */
	public function testInvalidMessageTypeThrowsException() : void
	{
		$logger   = new NullLogger();
		$eventBus = new EventBus( $logger );
		$eventBus->addEventHandlers( $this->getEventHandler() );

		$messageBuilder = new class implements BuildsMessages
		{
			public function buildMessage( DefinesMessage $messageHeader, array $packets ) : ProvidesMessageData
			{
				return new class implements ProvidesMessageData
				{
					use StringRepresenting;

					public function getMessageType() : IdentifiesMessageType
					{
						return new MessageType( 666 );
					}

					public function toString() : string
					{
						return 'Invalid message';
					}
				};
			}
		};

		$listener = new MessageQueueClientListener( $eventBus, $messageBuilder );
		$listener->setLogger( $logger );

		$stream       = new Stream( $this->serverSocket );
		$remoteStream = new Stream( $this->getRemoteClientSocket() );
		$clientStream = $stream->acceptConnection();

		$loop = $this->getMockBuilder( TracksStreams::class )->getMockForAbstractClass();

		$message = new MessageClientToServer( $this->getQueueName( 'Test-Queue' ), 'Unit-Test' );
		$remoteStream->write( $message->toString() );

		/** @var TracksStreams $loop */
		$listener->handleStreamActivity( $clientStream, $loop );

		$clientStream->close();
		$remoteStream->close();
	}
}
