<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Exceptions\WriteFailedException;
use PHPMQ\Server\Clients\Interfaces\CollectsClients;
use PHPMQ\Server\Endpoint\Events\ClientHasConnectedEvent;
use PHPMQ\Server\Endpoint\Events\ClientHasDisconnectedEvent;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Loggers\Monitoring\Constants\ServerMonitoring;
use Psr\Log\LoggerAwareTrait;

/**
 * Class ClientCollection
 * @package PHPMQ\Server\Clients
 */
final class ClientCollection implements CollectsClients
{
	use LoggerAwareTrait;

	/** @var array|Client[] */
	private $clients;

	/** @var DispatchesMessages */
	private $messageDispatcher;

	/** @var PublishesEvents */
	private $eventBus;

	public function __construct( DispatchesMessages $messageDispatcher, PublishesEvents $eventBus )
	{
		$this->clients           = [];
		$this->messageDispatcher = $messageDispatcher;
		$this->eventBus          = $eventBus;
	}

	public function add( Client $client ) : void
	{
		$this->clients[ $client->getClientId()->toString() ] = $client;

		$this->eventBus->publishEvent( new ClientHasConnectedEvent( $client ) );

		$this->logger->debug(
			sprintf( 'New client with ID %s connected', $client->getClientId()->toString() ),
			[
				'monitoring' => ServerMonitoring::CLIENT_CONNECTED,
			]
		);
	}

	public function remove( Client $client ) : void
	{
		$this->eventBus->publishEvent( new ClientHasDisconnectedEvent( $client ) );

		$this->logger->debug(
			sprintf( 'Client with ID %s disconnected', $client->getClientId()->toString() ),
			[
				'monitoring' => ServerMonitoring::CLIENT_DISCONNECTED,
			]
		);

		unset( $this->clients[ $client->getClientId()->toString() ] );
	}

	public function getActive() : array
	{
		if ( empty( $this->clients ) )
		{
			return [];
		}

		$reads  = [];
		$writes = $exepts = null;

		foreach ( $this->clients as $client )
		{
			$client->collectSocket( $reads );
		}

		if ( !@stream_select( $reads, $writes, $exepts, 0 ) )
		{
			return [];
		}

		return array_intersect_key( $this->clients, $reads );
	}

	public function dispatchMessages() : void
	{
		$clients = $this->clients;

		shuffle( $clients );

		foreach ( $clients as $client )
		{
			$this->dispatchMessagesToClient( $client );
		}
	}

	private function dispatchMessagesToClient( Client $client ) : void
	{
		try
		{
			$this->messageDispatcher->dispatchMessages( $client );
		}
		catch ( WriteFailedException $e )
		{
			$this->remove( $client );
		}
	}

	public function shutDown() : void
	{
		foreach ( $this->clients as $client )
		{
			$client->shutDown();
		}
	}
}
