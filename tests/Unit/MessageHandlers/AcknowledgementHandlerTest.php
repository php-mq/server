<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\MessageHandlers;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\MessageHandlers\AcknowledgementHandler;
use hollodotme\PHPMQ\Protocol\Messages\Acknowledgement;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\SocketMocking;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\StorageMocking;
use hollodotme\PHPMQ\Types\Message;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class AcknowledgementHandlerTest
 * @package hollodotme\PHPMQ\Tests\Unit\MessageHandlers
 */
final class AcknowledgementHandlerTest extends TestCase
{
	use StorageMocking;
	use SocketMocking;

	public function setUp() : void
	{
		$this->setUpStorage();
		$this->setUpSockets();
	}

	public function tearDown() : void
	{
		$this->tearDownSockets();
		$this->tearDownStorage();
	}

	public function testAcceptsAcknowledgementMessages() : void
	{
		$handler = new AcknowledgementHandler( $this->messageQueue );
		$handler->setLogger( new NullLogger() );
		$messageType = new MessageType( MessageType::ACKNOWLEDGEMENT );

		$this->assertTrue( $handler->acceptsMessageType( $messageType ) );
	}

	public function testCanHandleAcknowledgement() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$client    = new Client( ClientId::generate(), $this->socketClient, new MessageBuilder() );
		$client->updateConsumptionInfo( $queueName, 1 );

		$messageId       = MessageId::generate();
		$message         = new Message( $messageId, 'Unit-Test' );
		$acknowledgement = new Acknowledgement( $queueName, $messageId );
		$messageHandler  = new AcknowledgementHandler( $this->messageQueue );
		$messageHandler->setLogger( new NullLogger() );

		$this->messageQueue->enqueue( $queueName, $message );

		$client->consumeMessage( new MessageE2C( $messageId, $queueName, 'Unit-Test' ) );

		$this->messageQueue->markAsDispached( $queueName, $messageId );

		$this->assertFalse( $client->canConsumeMessages() );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );

		$this->assertTrue( $messageHandler->acceptsMessageType( $acknowledgement->getMessageType() ) );

		$messageHandler->handle( $acknowledgement, $client );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );

		$this->assertTrue( $client->canConsumeMessages() );
	}
}
