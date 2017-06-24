<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Servers;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Events\MessageQueue\ClientGotReadyForConsumingMessages;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Servers\MessageQueueServer;
use PHPMQ\Server\Servers\ServerSocket;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageQueueServerTest
 * @package PHPMQ\Server\Tests\Unit\Servers
 */
final class MessageQueueServerTest extends TestCase
{
	use SocketMocking;

	public function testCanGetClientConnectionEvent() : void
	{
		$server = new MessageQueueServer( new ServerSocket( $this->getSocketAddress() ) );
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
	 * @param string $message
	 * @param string $expectedEventClass
	 *
	 * @dataProvider messageProvider
	 */
	public function testCanGetCommandEvents( string $message, string $expectedEventClass ) : void
	{
		$server = new MessageQueueServer( new ServerSocket( $this->getSocketAddress() ) );
		$server->setLogger( new NullLogger() );
		$server->start();

		$remoteClient = $this->getRemoteClientSocket();

		fwrite( $remoteClient, $message );

		$events = iterator_to_array( $server->getEvents() );

		$this->assertInstanceOf( $expectedEventClass, $events[0] );

		$events = iterator_to_array( $server->getEvents() );
		$this->assertCount( 0, $events );

		$server->stop();
		fclose( $remoteClient );
	}

	public function messageProvider() : array
	{
		return [
			[
				'message'            => new MessageC2E( new QueueName( 'Test-Queue' ), 'Unit-Test' ),
				'expectedEventClass' => ClientSentMessageC2E::class,
			],
			[
				'message'            => new Acknowledgement( new QueueName( 'Test' ), new MessageId( 'Test' ) ),
				'expectedEventClass' => ClientSentAcknowledgement::class,
			],
			[
				'message'            => new ConsumeRequest( new QueueName( 'Test-Queue' ), 1 ),
				'expectedEventClass' => ClientSentConsumeResquest::class,
			],
		];
	}

	public function testCanGetReadyForConsumptionRequest() : void
	{
		$server = new MessageQueueServer( new ServerSocket( $this->getSocketAddress() ) );
		$server->setLogger( new NullLogger() );
		$server->start();

		$remoteClient = $this->getRemoteClientSocket();

		$queueName = new QueueName( 'Test-Queue' );

		fwrite( $remoteClient, (new ConsumeRequest( $queueName, 1 ))->toString() );

		$events = iterator_to_array( $server->getEvents() );

		/** @var ClientSentConsumeResquest $event */
		$event = $events[0];

		$this->assertInstanceOf( ClientSentConsumeResquest::class, $event );

		$client = $event->getClient();
		$client->updateConsumptionInfo( new ConsumptionInfo( $queueName, 1 ) );

		$events = iterator_to_array( $server->getEvents() );

		$this->assertInstanceOf( ClientGotReadyForConsumingMessages::class, $events[0] );

		$server->stop();
		fclose( $remoteClient );
	}
}
