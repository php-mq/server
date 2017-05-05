<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\MessageHandlers;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\MessageHandlers\MessageC2EHandler;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Messages\MessageC2E;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\SocketMocking;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\StorageMocking;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageC2EHandlerTest
 * @package hollodotme\PHPMQ\Tests\Unit\MessageHandlers
 */
final class MessageC2EHandlerTest extends TestCase
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

	public function testAcceptsMessageC2EMessages() : void
	{
		$handler = new MessageC2EHandler( $this->messageQueue );
		$handler->setLogger( new NullLogger() );
		$messageType = new MessageType( MessageType::MESSAGE_C2E );

		$this->assertTrue( $handler->acceptsMessageType( $messageType ) );
	}

	public function testCanHandleMessage() : void
	{
		$client = new Client( ClientId::generate(), $this->socketClient, new MessageBuilder() );

		$queueName         = new QueueName( 'Test-Queue' );
		$messageC2E        = new MessageC2E( $queueName, 'Unit-Test' );
		$messageC2EHandler = new MessageC2EHandler( $this->messageQueue );
		$messageC2EHandler->setLogger( new NullLogger() );

		$this->assertTrue( $messageC2EHandler->acceptsMessageType( $messageC2E->getMessageType() ) );

		$messageC2EHandler->handle( $messageC2E, $client );

		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountUndispatched() );
		$this->assertSame( 0, $this->messageQueue->getQueueStatus( $queueName )->getCountDispatched() );
		$this->assertSame( 1, $this->messageQueue->getQueueStatus( $queueName )->getCountTotal() );
	}
}
