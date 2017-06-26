<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events\MessageQueue;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Events\Interfaces\ProvidesMessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;

/**
 * Class ClientSentAcknowledgement
 * @package PHPMQ\Server\Events\MessageQueue
 */
final class ClientSentAcknowledgement implements CarriesEventData, ProvidesMessageQueueClient
{
	/** @var MessageQueueClient */
	private $messageQueueClient;

	/** @var Acknowledgement */
	private $acknowledgement;

	public function __construct( MessageQueueClient $client, Acknowledgement $acknowledgement )
	{
		$this->messageQueueClient = $client;
		$this->acknowledgement    = $acknowledgement;
	}

	public function getMessageQueueClient() : MessageQueueClient
	{
		return $this->messageQueueClient;
	}

	public function getAcknowledgement() : Acknowledgement
	{
		return $this->acknowledgement;
	}
}
