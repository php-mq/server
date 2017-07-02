<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Servers\Interfaces\CommunicatesWithServer;
use PHPMQ\Server\Servers\Interfaces\TracksClients;

/**
 * Class ClientPool
 * @package PHPMQ\Server\Clients
 */
final class ClientPool implements TracksClients
{
	/** @var array|CommunicatesWithServer[] */
	private $clients = [];

	public function add( CommunicatesWithServer $client ) : void
	{
		$clientId                               = $client->getClientId();
		$this->clients[ $clientId->toString() ] = $client;
	}

	public function remove( CommunicatesWithServer $client ) : void
	{
		$clientId = $client->getClientId();

		$client->shutDown();

		unset( $this->clients[ $clientId->toString() ] );
	}

	/**
	 * @return iterable|CommunicatesWithServer[]
	 */
	public function getActive() : iterable
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

		if ( !@stream_select( $reads, $writes, $exepts, 0, 200000 ) )
		{
			return [];
		}

		$activeClients = array_intersect_key( $this->clients, $reads );

		print_r( $activeClients );

		return $activeClients;
	}

	public function getShuffled() : iterable
	{
		$clients = $this->clients;

		shuffle( $clients );

		return $clients;
	}

	public function shutDown() : void
	{
		foreach ( $this->clients as $client )
		{
			$client->shutDown();
		}

		$this->clients = [];
	}
}
