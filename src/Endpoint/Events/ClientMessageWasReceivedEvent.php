<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Events;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;

/**
 * Class ClientMessageWasReceivedEvent
 * @package PHPMQ\Server\Endpoint\Events
 */
final class ClientMessageWasReceivedEvent implements CarriesEventData
{
	/** @var CarriesInformation */
	private $message;

	/** @var Client */
	private $client;

	public function __construct( CarriesInformation $message, Client $client )
	{
		$this->message = $message;
		$this->client  = $client;
	}

	public function getMessage() : CarriesInformation
	{
		return $this->message;
	}

	public function getClient() : Client
	{
		return $this->client;
	}
}
