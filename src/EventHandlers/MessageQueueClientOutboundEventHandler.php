<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers;

use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Events\MessageQueue\ClientGotReadyForConsumingMessages;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;

/**
 * Class MessageQueueClientOutboundEventHandler
 * @package PHPMQ\Server\EventHandlers
 */
final class MessageQueueClientOutboundEventHandler extends AbstractEventHandler
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
			ClientGotReadyForConsumingMessages::class,
		];
	}

	protected function whenMessageQueueClientGotReadyForConsumingMessages(
		ClientGotReadyForConsumingMessages $event
	) : void
	{
		$client          = $event->getMessageQueueClient();
		$consumptionInfo = $client->getConsumptionInfo();

		if ( !$consumptionInfo->canConsume() )
		{
			return;
		}

		$this->dispatchMessagesToClient( $client, $consumptionInfo );
	}

	private function dispatchMessagesToClient(
		MessageQueueClient $client,
		ProvidesConsumptionInfo $consumptionInfo
	) : void
	{
		$queueName    = $consumptionInfo->getQueueName();
		$messageCount = $consumptionInfo->getMessageCount();
		$messages     = $this->storage->getUndispatched( $queueName, $messageCount );

		foreach ( $messages as $message )
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

			$this->logger->debug( '<fg:yellow>√ Message marked as dispatched: ' . $message->getMessageId() . '<:fg>' );
		}
	}
}
