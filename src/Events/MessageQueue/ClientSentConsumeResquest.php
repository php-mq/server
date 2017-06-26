<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Events\Interfaces\ProvidesMessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\ConsumeRequest;

/**
 * Class ClientSentConsumeResquest
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentConsumeResquest implements CarriesEventData, ProvidesMessageQueueClient
{
	/** @var MessageQueueClient */
	private $messageQueueClient;

	/** @var ConsumeRequest */
	private $consumeRequest;

	public function __construct( MessageQueueClient $client, ConsumeRequest $consumeRequest )
	{
		$this->messageQueueClient = $client;
		$this->consumeRequest     = $consumeRequest;
	}

	public function getMessageQueueClient() : MessageQueueClient
	{
		return $this->messageQueueClient;
	}

	public function getConsumeRequest() : ConsumeRequest
	{
		return $this->consumeRequest;
	}
}
