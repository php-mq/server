<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint;

use PHPMQ\Server\Clients\Client;
use PHPMQ\Server\Endpoint\Events\ClientMessageWasReceivedEvent;
use PHPMQ\Server\Endpoint\Interfaces\HandlesMessages;
use PHPMQ\Server\Interfaces\PublishesEvents;
use PHPMQ\Server\Protocol\Interfaces\CarriesInformation;

/**
 * Class MessageHandler
 * @package PHPMQ\Server\Endpoint
 */
final class MessageHandler implements HandlesMessages
{
	/** @var PublishesEvents */
	private $eventBus;

	public function __construct( PublishesEvents $eventBus )
	{
		$this->eventBus = $eventBus;
	}

	public function handle( CarriesInformation $message, Client $client ) : void
	{
		$this->eventBus->publishEvent( new ClientMessageWasReceivedEvent( $message, $client ) );
	}
}
