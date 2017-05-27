<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Tests\Unit\Clients;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Clients\ClientCollection;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Interfaces\ConsumesMessages;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;
use PHPMQ\Server\Protocol\Messages\MessageBuilder;
use PHPMQ\Server\Tests\Unit\Fixtures\Traits\SocketMocking;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareTrait;

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
		$client = new Client( ClientId::generate(), $this->socketClient, new MessageBuilder() );

		$dispatcher = new class implements DispatchesMessages
		{
			use LoggerAwareTrait;

			public function dispatchMessages( ConsumesMessages $client ) : void
			{
				echo 'Dispatching.';
			}
		};

		$collection = new ClientCollection( $dispatcher );

		$collection->add( $client );

		$collection->dispatchMessages();

		$collection->remove( $client );

		$collection->dispatchMessages();

		$this->expectOutputString( 'Dispatching.' );
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
		$client     = new Client( ClientId::generate(), $this->socketClient, new MessageBuilder() );
		$dispatcher = $this->getEmptyDispatcher();
		$collection = new ClientCollection( $dispatcher );

		$this->assertCount( 0, $collection->getActive() );

		$collection->add( $client );

		$this->assertCount( 0, $collection->getActive() );
	}
}