<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Events;

use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;

/**
 * Class MessageQueueClientSentAcknowledgement
 * @package PHPMQ\Server\Events
 */
final class MessageQueueClientSentAcknowledgement implements CarriesEventData
{
	/** @var MessageQueueClient */
	private $client;

	/** @var Acknowledgement */
	private $acknowledgement;

	public function __construct( MessageQueueClient $client, Acknowledgement $acknowledgement )
	{
		$this->client          = $client;
		$this->acknowledgement = $acknowledgement;
	}

	public function getClient() : MessageQueueClient
	{
		return $this->client;
	}

	public function getAcknowledgement() : Acknowledgement
	{
		return $this->acknowledgement;
	}
}
