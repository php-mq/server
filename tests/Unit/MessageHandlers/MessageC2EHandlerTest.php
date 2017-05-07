<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\MessageHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\MessageHandlers\MessageC2EHandler;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Messages\MessageC2E;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\StorageMocking;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class MessageC2EHandlerTest
 * @package PHPMQ\Server\Tests\Unit\MessageHandlers
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
