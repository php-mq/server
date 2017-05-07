<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Interfaces\CollectsClients;
use PHPMQ\Server\Clients\Interfaces\HandlesClientDisconnect;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;
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

	/** @var array|HandlesClientDisconnect[] */
	private $disconnectHandlers;

	public function __construct( DispatchesMessages $messageDispatcher )
	{
		$this->clients            = [];
		$this->messageDispatcher  = $messageDispatcher;
		$this->disconnectHandlers = [];
	}

	public function addDisconnectHandlers( HandlesClientDisconnect ...$clientDisconnectHandlers ) : void
	{
		foreach ( $clientDisconnectHandlers as $clientDisconnectHandler )
		{
			$clientDisconnectHandler->setLogger( $this->logger );

			$this->disconnectHandlers[] = $clientDisconnectHandler;
		}
	}

	public function add( Client $client ) : void
	{
		$this->clients[ $client->getClientId()->toString() ] = $client;

		$this->logger->debug( 'New client connected: ' . $client->getClientId() );
	}

	public function remove( Client $client ) : void
	{
		$this->logger->debug( 'Client disconnected: ' . $client->getClientId() );

		$this->handleDisconnect( $client );

		unset( $this->clients[ $client->getClientId()->toString() ] );
	}

	private function handleDisconnect( Client $client ) : void
	{
		foreach ( $this->disconnectHandlers as $handler )
		{
			$handler->handleDisconnect( $client );
		}
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

		socket_select( $reads, $writes, $exepts, 0 );

		return array_intersect_key( $this->clients, $reads );
	}

	public function dispatchMessages() : void
	{
		foreach ( $this->clients as $client )
		{
			$this->messageDispatcher->dispatchMessages( $client );
		}
	}
}
