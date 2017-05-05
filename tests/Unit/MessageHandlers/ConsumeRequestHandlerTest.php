<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Tests\Unit\MessageHandlers;

use hollodotme\PHPMQ\Clients\Client;
use hollodotme\PHPMQ\Clients\Types\ClientId;
use hollodotme\PHPMQ\MessageHandlers\ConsumeRequestHandler;
use hollodotme\PHPMQ\Protocol\Messages\ConsumeRequest;
use hollodotme\PHPMQ\Protocol\Messages\MessageBuilder;
use hollodotme\PHPMQ\Protocol\Types\MessageType;
use hollodotme\PHPMQ\Tests\Unit\Fixtures\Traits\SocketMocking;
use hollodotme\PHPMQ\Types\QueueName;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class ConsumeRequestHandlerTest
 * @package hollodotme\PHPMQ\Tests\Unit\MessageHandlers
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
