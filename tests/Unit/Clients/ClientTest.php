<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Clients;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\SocketMocking;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 * @package hollodotme\PHPMQ\Tests\Unit\Clients
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

	public function testClientIsNotDisconnectedAfterConstruction() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->socketClient, new MessageBuilder() );

		$this->assertSame( $clientId, $client->getClientId() );
		$this->assertSame( (string)$clientId, $client->getClientId()->toString() );
		$this->assertFalse( $client->isDisconnected() );
	}

	public function testCanCollectSocket() : void
	{
		$clientId        = ClientId::generate();
		$socket          = $this->socketClient;
		$client          = new Client( $clientId, $socket, new MessageBuilder() );
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
		$client   = new Client( $clientId, $this->socketClient, new MessageBuilder() );

		$this->assertFalse( $client->canConsumeMessages() );
		$this->assertSame( 0, $client->getConsumptionMessageCount() );
	}

	public function testCanUpdateConsumptionCount() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$clientId  = ClientId::generate();
		$client    = new Client( $clientId, $this->socketClient, new MessageBuilder() );

		$client->updateConsumptionInfo( $queueName, 5 );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 5, $client->getConsumptionMessageCount() );

		$client->updateConsumptionInfo( $queueName, 3 );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 3, $client->getConsumptionMessageCount() );

		$client->updateConsumptionInfo( $queueName, 0 );

		$this->assertFalse( $client->canConsumeMessages() );
		$this->assertSame( 0, $client->getConsumptionMessageCount() );
	}

	public function testCanConsumeMessages() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$clientId  = ClientId::generate();
		$client    = new Client( $clientId, $this->socketClient, new MessageBuilder() );

		$client->updateConsumptionInfo( $queueName, 5 );

		$messageId = MessageId::generate();
		$message = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 4, $client->getConsumptionMessageCount() );
	}

	public function testCanAcknowledgeMessages() : void
	{
		$queueName = new QueueName( 'Test-Queue' );
		$clientId  = ClientId::generate();
		$client    = new Client( $clientId, $this->socketClient, new MessageBuilder() );

		$client->updateConsumptionInfo( $queueName, 5 );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 4, $client->getConsumptionMessageCount() );

		$client->acknowledgeMessage( $queueName, $messageId );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 5, $client->getConsumptionMessageCount() );
	}
}
