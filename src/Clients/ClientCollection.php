<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Interfaces\CollectsClients;
use PHPMQ\Server\Endpoint\Interfaces\DispatchesMessages;

/**
 * Class ClientCollection
 * @package PHPMQ\Server\Clients
 */
final class ClientCollection implements CollectsClients
{
	/** @var array|Client[] */
	private $clients;

	/** @var DispatchesMessages */
	private $messageDispatcher;

	public function __construct( DispatchesMessages $messageDispatcher )
	{
		$this->clients           = [];
		$this->messageDispatcher = $messageDispatcher;
	}

	public function add( Client $client ) : void
	{
		$this->clients[ $client->getClientId()->toString() ] = $client;
	}

	public function remove( Client $client ) : void
	{
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

		socket_select( $reads, $writes, $exepts, 0 );

		return array_intersect_key( $this->clients, $reads );
	}

	public function dispatchMessages() : void
	{
		$clients = $this->clients;

		shuffle( $clients );

		foreach ( $clients as $client )
		{
			$this->messageDispatcher->dispatchMessages( $client );
		}
	}
}
