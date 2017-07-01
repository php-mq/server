<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\Interfaces\IdentifiesClient;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Protocol\Interfaces\CarriesMessageData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageQueueClientTest
 * @package PHPMQ\MessageQueueServer\Tests\Unit\Clients
 */
final class MessageQueueClientTest extends TestCase
{
	use SocketMocking;

	public function setUp() : void
	{
		$this->setUpServerSocket();
	}

	public function tearDown() : void
	{
		$this->tearDownServerSocket();
	}

	public function testCanCollectSocket() : void
	{
		$remoteClient    = $this->getRemoteClientSocket();
		$clientId        = $this->getClientId();
		$serverClient    = $this->getServerClientSocket();
		$client          = new MessageQueueClient( $clientId, $serverClient );
		$expectedSockets = [
			$clientId->toString() => $serverClient,
		];

		$sockets = [];

		$client->collectSocket( $sockets );

		$this->assertSame( $expectedSockets, $sockets );

		fclose( $remoteClient );
	}

	private function getClientId() : IdentifiesClient
	{
		return new ClientId( bin2hex( random_bytes( 16 ) ) );
	}

	public function testCannotConsumeMessagesAfterConstruction() : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		$this->assertFalse( $client->getConsumptionInfo()->canConsume() );
		$this->assertSame( 0, $client->getConsumptionInfo()->getMessageCount() );

		fclose( $remoteClient );
	}

	/**
	 * @expectedException \PHPMQ\Server\Clients\Exceptions\ClientHasPendingMessagesException
	 */
	public function testWhenHavingPendingMessagesUpdateConsumptionThrowsException() : void
	{
		$queueName    = new QueueName( 'Test-Queue' );
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$consumptionInfo = new ConsumptionInfo( new QueueName( 'Other-Queue' ), 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		fclose( $remoteClient );
	}

	public function testCanConsumeMessages() : void
	{
		$queueName    = new QueueName( 'Test-Queue' );
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->consumeMessage( $message );

		$this->assertTrue( $client->getConsumptionInfo()->canConsume() );
		$this->assertSame( 4, $client->getConsumptionInfo()->getMessageCount() );

		fclose( $remoteClient );
	}

	/**
	 * @expectedException \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	public function testConsumingMessageThrowsExceptionWhenSocketIsClosed() : void
	{
		$queueName    = new QueueName( 'Test-Queue' );
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		$consumptionInfo = new ConsumptionInfo( $queueName, 5 );
		$client->updateConsumptionInfo( $consumptionInfo );

		$messageId = MessageId::generate();
		$message   = new MessageE2C( $messageId, $queueName, 'Unit-Test' );

		$client->shutDown();

		$client->consumeMessage( $message );

		fclose( $remoteClient );
	}

	public function testCanReadBytes() : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		fwrite( $remoteClient, 'Unit-Test' );

		$bytes = $client->read( 9 );

		$this->assertSame( 'Unit-Test', $bytes );

		fclose( $remoteClient );
	}

	public function testCanCheckForUnreadData() : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		fwrite( $remoteClient, 'Unit-Test' );

		$client->read( 8 );

		$this->assertTrue( $client->hasUnreadData() );

		fclose( $remoteClient );
	}

	public function testCanGetClientId() : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		$this->assertSame( $clientId, $client->getClientId() );

		fclose( $remoteClient );
	}

	/**
	 * @dataProvider messageProvider
	 *
	 * @param CarriesMessageData $message
	 */
	public function testCanReadMessage( CarriesMessageData $message ) : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		fwrite( $remoteClient, $message->toString() );

		$readMessage = $client->readMessage( new MessageBuilder() );

		$this->assertEquals( $message, $readMessage );

		fclose( $remoteClient );
	}

	public function messageProvider() : array
	{
		return [
			[
				new ConsumeRequest( new QueueName( 'Test-Queue' ), 42 ),
			],
			[
				new MessageC2E( new QueueName( 'Test-Queue' ), 'Unit-Test-Message' ),
			],
			[
				new Acknowledgement( new QueueName( 'Test-Queue' ), MessageId::generate() ),
			],
		];
	}

	/**
	 * @expectedException \PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException
	 */
	public function testClientThrowsExceptionWhenDisconnected() : void
	{
		$remoteClient = $this->getRemoteClientSocket();
		$clientId     = $this->getClientId();
		$serverClient = $this->getServerClientSocket();
		$client       = new MessageQueueClient( $clientId, $serverClient );

		fclose( $remoteClient );

		$client->readMessage( new MessageBuilder() );
	}
}
