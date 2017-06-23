<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientConnected
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientConnected implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $messageQueueClient;

	public function __construct( MessageQueueClient $messageQueueClient )
	{
		$this->messageQueueClient = $messageQueueClient;
	}

	public function getMessageQueueClient() : MessageQueueClient
	{
		return $this->messageQueueClient;
	}
}
