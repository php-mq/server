<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Clients\ClientCollection;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\ListensToEvents;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class ClientCollectionTest
 * @package PHPMQ\Server\Tests\Unit\Clients
 */
final class ClientCollectionTest extends TestCase
{
	use SocketMocking;

	protected function setUp() : void
	{
		$this->setUpSockets();
	}

	protected function tearDown() : void
	{
		$this->tearDownSockets();
	}

	public function testCanDispatchMessages() : void
	{
		$client = new MessageQueueClient( ClientId::generate(), $this->socketClient, new MessageBuilder() );

		$dispatcher = new class implements DispatchesMessages
		{
			use LoggerAwareTrait;

			public function dispatchMessages( ConsumesMessages $client ) : void
			{
				echo 'Dispatching.';
			}
		};

		$eventBus = $this->getEmptyEventBus();

		$collection = new ClientCollection( $dispatcher, $eventBus );
		$collection->setLogger( new NullLogger() );

		$collection->add( $client );

		$collection->dispatchMessages();

		$collection->remove( $client );

		$collection->dispatchMessages();

		$this->expectOutputString( 'Dispatching.' );
	}

	private function getEmptyEventBus() : PublishesEvents
	{
		return new class implements PublishesEvents
		{
			use LoggerAwareTrait;

			public function addEventListeners( ListensToEvents ...$eventListeners ) : void
			{
				// TODO: Implement addEventListeners() method.
			}

			public function publishEvent( CarriesEventData $event ) : void
			{
				// TODO: Implement publishEvent() method.
			}

		};
	}

	private function getEmptyDispatcher() : DispatchesMessages
	{
		return new class implements DispatchesMessages
		{
			use LoggerAwareTrait;

			public function dispatchMessages( ConsumesMessages $client ) : void
			{
			}
		};
	}

	public function testCanGetActiveClients() : void
	{
		$client     = new MessageQueueClient( ClientId::generate(), $this->socketClient, new MessageBuilder() );
		$dispatcher = $this->getEmptyDispatcher();
		$eventBus   = $this->getEmptyEventBus();
		$collection = new ClientCollection( $dispatcher, $eventBus );
		$collection->setLogger( new NullLogger() );

		$this->assertCount( 0, $collection->getActive() );

		$collection->add( $client );

		$this->assertCount( 0, $collection->getActive() );
	}
}
