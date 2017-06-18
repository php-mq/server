<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Events;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;

/**
 * Class ConsumeRequestWasReceivedEvent
 * @package PHPMQ\Server\Endpoint\Events
 */
final class ConsumeRequestWasReceivedEvent implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $client;

	/** @var ConsumeRequest */
	private $consumeRequest;

	public function __construct( MessageQueueClient $client, ConsumeRequest $consumeRequest )
	{
		$this->client         = $client;
		$this->consumeRequest = $consumeRequest;
	}

	public function getClient() : MessageQueueClient
	{
		return $this->client;
	}

	public function getConsumeRequest() : ConsumeRequest
	{
		return $this->consumeRequest;
	}
}
