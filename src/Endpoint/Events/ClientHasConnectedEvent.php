<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Events;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientHasConnectedEvent
 * @package PHPMQ\Server\Endpoint\Events
 */
final class ClientHasConnectedEvent implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $client;

	public function __construct( MessageQueueClient $client )
	{
		$this->client = $client;
	}

	public function getClient() : MessageQueueClient
	{
		return $this->client;
	}
}
