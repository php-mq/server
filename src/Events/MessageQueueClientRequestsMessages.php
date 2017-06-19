<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Class MessageQueueClientRequestsMessages
 * @package PHPMQ\Server\Events
 */
final class MessageQueueClientRequestsMessages implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $messageQueueClient;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var int */
	private $messageCount;

	public function __construct( MessageQueueClient $messageQueueClient, IdentifiesQueue $queueName, int $messageCount )
	{
		$this->messageQueueClient = $messageQueueClient;
		$this->queueName          = $queueName;
		$this->messageCount       = $messageCount;
	}

	public function getMessageQueueClient() : MessageQueueClient
	{
		return $this->messageQueueClient;
	}

	public function getQueueName() : IdentifiesQueue
	{
		return $this->queueName;
	}

	public function getMessageCount() : int
	{
		return $this->messageCount;
	}
}
