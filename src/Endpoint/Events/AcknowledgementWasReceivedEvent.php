<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\Events;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Interfaces\CarriesEventData;
use PHPMQ\Server\Protocol\Messages\Acknowledgement;

/**
 * Class AcknowledgementWasReceivedEvent
 * @package PHPMQ\Server\Endpoint\Events
 */
final class AcknowledgementWasReceivedEvent implements CarriesEventData
{
	/** @var Client */
	private $client;

	/** @var Acknowledgement */
	private $acknowledgement;

	public function __construct( Client $client, Acknowledgement $acknowledgement )
	{
		$this->client          = $client;
		$this->acknowledgement = $acknowledgement;
	}

	public function getClient() : Client
	{
		return $this->client;
	}

	public function getAcknowledgement() : Acknowledgement
	{
		return $this->acknowledgement;
	}
}
