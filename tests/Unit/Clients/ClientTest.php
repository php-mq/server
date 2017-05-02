<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\Clients;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\Protocol\Messages\MessageE2C;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 * @package hollodotme\PHPMQ\Tests\Unit\Clients
 */
final class ClientTest extends TestCase
{
	private const SOCKET_PATH = '/tmp/mock.sock';

	/** @var resource */
	private $socketServer;

	/** @var resource */
	private $socketClient;

	public function setUp() : void
	{
		$this->socketServer = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		@unlink( self::SOCKET_PATH );
		socket_bind( $this->socketServer, self::SOCKET_PATH );
		socket_listen( $this->socketServer, SOMAXCONN );

		$this->socketClient = socket_create( AF_UNIX, SOCK_STREAM, 0 );
		socket_connect( $this->socketClient, self::SOCKET_PATH );
	}

	public function tearDown() : void
	{
		socket_shutdown( $this->socketClient );
		socket_close( $this->socketClient );
	}

	public function testClientIsNotDisconnectedAfterConstruction() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->socketClient );

		$this->assertSame( $clientId, $client->getClientId() );
		$this->assertSame( (string)$clientId, $client->getClientId()->toString() );
		$this->assertFalse( $client->isDisconnected() );
	}

	public function testCanCollectSocket() : void
	{
		$clientId        = ClientId::generate();
		$socket          = $this->socketClient;
		$client          = new Client( $clientId, $socket );
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
		$client   = new Client( $clientId, $this->socketClient );

		$this->assertFalse( $client->canConsumeMessages() );
		$this->assertSame( 0, $client->getConsumableMessageCount() );
	}

	public function testCanUpdateConsumptionCount() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->socketClient );

		$client->updateConsumptionCount( 5 );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 5, $client->getConsumableMessageCount() );

		$client->updateConsumptionCount( 3 );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 3, $client->getConsumableMessageCount() );

		$client->updateConsumptionCount( 0 );

		$this->assertFalse( $client->canConsumeMessages() );
		$this->assertSame( 0, $client->getConsumableMessageCount() );
	}

	public function testCanConsumeMessages() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->socketClient );

		$client->updateConsumptionCount( 5 );

		$messageId = MessageId::generate();
		$queueName = new QueueName( 'Test-Queue' );

		$message = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 4, $client->getConsumableMessageCount() );
	}

	public function testCanAcknowledgeMessages() : void
	{
		$clientId = ClientId::generate();
		$client   = new Client( $clientId, $this->socketClient );

		$client->updateConsumptionCount( 5 );

		$messageId = MessageId::generate();
		$queueName = new QueueName( 'Test-Queue' );

		$message = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 4, $client->getConsumableMessageCount() );

		$client->acknowledgeMessage( $messageId );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( 5, $client->getConsumableMessageCount() );
	}
}
