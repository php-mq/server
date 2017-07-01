<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\Exceptions\ClientDisconnectedException;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Events\MessageQueue\ClientGotReadyForConsumingMessages;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Storage\Interfaces\ProvidesMessageData;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class ClientOutboundEventHandler
 * @package PHPMQ\Server\EventHandlers\MessageQueue
 */
final class ClientOutboundEventHandler extends AbstractEventHandler
{
	/** @var StoresMessages */
	private $storage;

	/** @var CollectsServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct( StoresMessages $storage, CollectsServerMonitoringInfo $serverMonitoringInfo )
	{
		$this->storage              = $storage;
		$this->serverMonitoringInfo = $serverMonitoringInfo;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientGotReadyForConsumingMessages::class,
		];
	}

	protected function whenClientGotReadyForConsumingMessages( ClientGotReadyForConsumingMessages $event ) : void
	{
		$client          = $event->getMessageQueueClient();
		$consumptionInfo = $client->getConsumptionInfo();

		if ( !$consumptionInfo->canConsume() )
		{
			return;
		}

		try
		{
			$this->dispatchMessages( $client, $consumptionInfo );
		}
		catch ( ClientDisconnectedException $e )
		{
			$clientPool = $event->getClientPool();
			$clientPool->remove( $client );
		}
	}

	private function dispatchMessages( MessageQueueClient $client, ProvidesConsumptionInfo $consumptionInfo ) : void
	{
		$queueName    = $consumptionInfo->getQueueName();
		$messageCount = $consumptionInfo->getMessageCount();
		$messages     = $this->storage->getUndispatched( $queueName, $messageCount );

		foreach ( $messages as $message )
		{
			$this->dispatchMessage( $client, $queueName, $message );
		}
	}

	private function dispatchMessage(
		MessageQueueClient $client,
		IdentifiesQueue $queueName,
		ProvidesMessageData $message
	) : void
	{
		$this->logger->debug(
			sprintf(
				'<fg:blue>»» Dispatching messages %s to client %s.<:fg>',
				$message->getMessageId(),
				$client->getClientId()
			)
		);

		$messageE2C = new MessageE2C( $message->getMessageId(), $queueName, $message->getContent() );

		$client->consumeMessage( $messageE2C );

		$this->logger->debug( '<fg:green>√ Message sent: ' . $message->getMessageId() . '<:fg>' );

		$this->storage->markAsDispached( $queueName, $message->getMessageId() );

		$this->serverMonitoringInfo->markMessageAsDispatched( $queueName, $message->getMessageId() );

		$this->logger->debug( '<fg:yellow>√ Message marked as dispatched: ' . $message->getMessageId() . '<:fg>' );
	}
}
