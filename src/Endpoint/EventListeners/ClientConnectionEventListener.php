<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Endpoint\EventListeners;

use PHPMQ\Server\Endpoint\Events\ClientHasConnectedEvent;
use PHPMQ\Server\Endpoint\Events\ClientHasDisconnectedEvent;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class ClientConnectionEventListener
 * @package PHPMQ\Server\Endpoint\EventListeners
 */
final class ClientConnectionEventListener extends AbstractEventListener
{
	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientHasConnectedEvent::class,
			ClientHasDisconnectedEvent::class,
		];
	}

	protected function whenClientHasConnected( ClientHasConnectedEvent $event ) : void
	{
		$client = $event->getClient();
	}

	protected function whenClientHasDisconnected( ClientHasDisconnectedEvent $event ) : void
	{
		$client = $event->getClient();

		$consumptionInfo = $client->getConsumptionInfo();
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );

			$consumptionInfo->removeMessageId( $messageId );
		}
	}
}
