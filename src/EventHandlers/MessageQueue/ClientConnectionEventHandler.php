<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\MessageQueue\ClientConnected;
use PHPMQ\Server\Events\MessageQueue\ClientDisconnected;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class ClientConnectionEventHandler
 * @package PHPMQ\Server\EventHandlers\MessageQueue
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
			ClientConnected::class,
			ClientDisconnected::class,
		];
	}

	protected function whenClientConnected( ClientConnected $event ): void
	{
		$client = $event->getMessageQueueClient();

		$this->logger->debug( 'New message queue client connected: ' . $client->getClientId() );
	}

	protected function whenClientDisconnected( ClientDisconnected $event ): void
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

		$this->logger->debug( 'Message queue client disconnected: ' . $client->getClientId() );
	}
}