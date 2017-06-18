<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 * @package PHPMQ\Server\Tests\Unit\Clients
 */
final class ClientTest extends TestCase
{
	use SocketMocking;

	public function setUp() : void
	{
		$this->setUpSockets();
	}

	public function tearDown() : void
	{
		$this->tearDownSockets();
	}

	public function testCanCollectSocket() : void
	{
		$clientId        = ClientId::generate();
		$socket          = $this->socketClient;
		$client          = new MessageQueueClient( $clientId, $socket, new MessageBuilder() );
		$expectedSockets = [
			$clientId->toString() => $socket,
		];

		$sockets = [];

		$client->collectSocket( $sockets );

		$this->assertSame( $expectedSockets, $sockets );
	}

	public function testCannotConsumeMessagesAfterConstruction() : void
	{
		$clientId = ClientId::generate();
		$client   = new MessageQueueClient( $clientId, $this->socketClient, new MessageBuilder() );

		$this->assertFalse( $client->getConsumptionInfo()->canConsume() );
		$this->assertSame( 0, $client->getConsumptionInfo()->getMessageCount() );
	}

	/**
	 * @expectedException \PHPMQ\Server\Clients\Exceptions\ClientHasPendingMessagesException
	 */
	public function testWhenHavingPendingMessagesUpdateConsumptionThrowsException() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$clientId  = ClientId::generate();
		$client    = new MessageQueueClient( $clientId, $this->socketClient, new MessageBuilder() );

		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$consumptionInfo = new ConsumptionInfo( new QueueName( 'Other-Queue' ), 5 );
		$client->updateConsumptionInfo( $consumptionInfo );
	}

	public function testCanConsumeMessages() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$clientId  = ClientId::generate();
		$client    = new MessageQueueClient( $clientId, $this->socketClient, new MessageBuilder() );

		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->getConsumptionInfo()->canConsume() );
		$this->assertSame( 4, $client->getConsumptionInfo()->getMessageCount() );
	}
}
