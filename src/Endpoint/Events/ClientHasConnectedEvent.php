<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Events;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientHasConnectedEvent
 * @package PHPMQ\Server\Endpoint\Events
 */
final class ClientHasConnectedEvent implements CarriesEventData
{
	/** @var Client */
	private $client;

	public function __construct( Client $client )
	{
		$this->client = $client;
	}

	public function getClient() : Client
	{
		return $this->client;
	}
}
