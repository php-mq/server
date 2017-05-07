<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\MessageHandlers;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\MessageHandlers\ConsumeRequestHandler;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Protocol\Types\MessageType;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPMQ\Server\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ConsumeRequestHandlerTest
 * @package PHPMQ\Server\Tests\Unit\MessageHandlers
 */
final class ConsumeRequestHandlerTest extends TestCase
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

	public function testAcceptsConsumeRequestMessages() : void
	{
		$handler = new ConsumeRequestHandler();
		$handler->setLogger( new NullLogger() );
		$messageType = new MessageType( MessageType::CONSUME_REQUEST );

		$this->assertTrue( $handler->acceptsMessageType( $messageType ) );
	}

	public function testCanHandleMessage() : void
	{
		$client         = new Client( ClientId::generate(), $this->socketClient, new MessageBuilder() );
		$queueName      = new QueueName( 'Test-Queue' );
		$consumeRequest = new ConsumeRequest( $queueName, 5 );
		$handler        = new ConsumeRequestHandler();
		$handler->setLogger( new NullLogger() );

		$handler->handle( $consumeRequest, $client );

		$this->assertTrue( $client->canConsumeMessages() );
		$this->assertSame( $queueName, $client->getConsumptionQueueName() );
		$this->assertSame( 5, $client->getConsumptionMessageCount() );
	}
}
