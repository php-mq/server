<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Events\Interfaces\ProvidesMessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Servers\Interfaces\TracksClients;

/**
 * Class ClientGotReadyForConsumingMessages
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientGotReadyForConsumingMessages implements CarriesEventData, ProvidesMessageQueueClient
{
	/** @var MessageQueueClient */
	private $messageQueueClient;

	/** @var TracksClients */
	private $clientPool;

	public function __construct( MessageQueueClient $messageQueueClient, TracksClients $clientPool )
	{
		$this->messageQueueClient = $messageQueueClient;
		$this->clientPool         = $clientPool;
	}

	public function getMessageQueueClient() : MessageQueueClient
	{
		return $this->messageQueueClient;
	}

	public function getClientPool() : TracksClients
	{
		return $this->clientPool;
	}
}
