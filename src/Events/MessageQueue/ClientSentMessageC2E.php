<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\MessageC2E;

/**
 * Class ClientSentMessageC2E
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentMessageC2E implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $client;

	/** @var MessageC2E */
	private $messageC2E;

	public function __construct( MessageQueueClient $client, MessageC2E $messageC2E )
	{
		$this->client     = $client;
		$this->messageC2E = $messageC2E;
	}

	public function getClient() : MessageQueueClient
	{
		return $this->client;
	}

	public function getMessageC2E() : MessageC2E
	{
		return $this->messageC2E;
	}
}
