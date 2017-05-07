<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\MessageHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\MessageHandlers\AcknowledgementHandler;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMocking;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class AcknowledgementHandlerTest
 * @package PHPMQ\Server\Tests\Unit\MessageHandlers
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

		$consumptionInfo = new ConsumptionInfo( $queueName, 1 );

		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId       = MessageId::generate();
		$message         = new Message( $messageId, 'Unit-Test' );
		$acknowledgement = new Acknowledgement( $queueName, $messageId );
		$messageHandler  = new AcknowledgementHandler( $this->messageQueue );
		$messageHandler->setLogger( new NullLogger() );

		$this->messageQueue->enqueue( $queueName, $message );

		$client->consumeMessage( new MessageE2C( $messageId, $queueName, 'Unit-Test' ) );

		$this->messageQueue->markAsDispached( $queueName, $messageId );

		$this->assertFalse( $client->getConsumptionInfo()->canConsume() );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );

		$this->assertTrue( $messageHandler->acceptsMessageType( $acknowledgement->getMessageType() ) );

		$messageHandler->handle( $acknowledgement, $client );

		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );

		$this->assertTrue( $client->getConsumptionInfo()->canConsume() );
	}
}
