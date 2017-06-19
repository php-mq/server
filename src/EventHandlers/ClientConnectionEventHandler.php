<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers;

use PHPMQ\Server\Events\MessageQueueClientConnected;
use PHPMQ\Server\Events\MessageQueueClientDisconnected;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class ClientConnectionEventHandler
 * @package PHPMQ\Server\EventHandlers
 */
final class ClientConnectionEventHandler extends AbstractEventHandler
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
			MessageQueueClientConnected::class,
			MessageQueueClientDisconnected::class,
		];
	}

	protected function whenMessageQueueClientConnected( MessageQueueClientConnected $event ) : void
	{
		$client = $event->getMessageQueueClient();
	}

	protected function whenMessageQueueClientDisconnected( MessageQueueClientDisconnected $event ) : void
	{
		$client = $event->getMessageQueueClient();

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
