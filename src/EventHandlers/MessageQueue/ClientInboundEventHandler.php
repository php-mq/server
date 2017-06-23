<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\MessageQueue
 */
final class ClientInboundEventHandler extends AbstractEventHandler
{
	/** @var StoresMessages */
	private $storage;

	public function __construct( StoresMessages $storage )
	{
		$this->storage = $storage;
	}

	protected function getAcceptedEvents(): array
	{
		return [
			ClientSentMessageC2E::class,
			ClientSentConsumeResquest::class,
			ClientSentAcknowledgement::class,
		];
	}

	protected function whenClientSentMessageC2E( ClientSentMessageC2E $event ): void
	{
		$messageC2E   = $event->getMessageC2E();
		$storeMessage = new Message( MessageId::generate(), $messageC2E->getContent() );

		$this->storage->enqueue( $messageC2E->getQueueName(), $storeMessage );
	}

	protected function whenClientSentConsumeResquest( ClientSentConsumeResquest $event ): void
	{
		$client         = $event->getClient();
		$consumeRequest = $event->getConsumeRequest();

		$this->logger->debug( 'Consume request received from client ' . $client->getClientId() );
		$this->logger->debug( '- For queue name: ' . $consumeRequest->getQueueName() );
		$this->logger->debug( '- Message count: ' . $consumeRequest->getMessageCount() );

		$this->cleanUpClientConsumption( $client );

		$client->updateConsumptionInfo(
			new ConsumptionInfo(
				$consumeRequest->getQueueName(),
				$consumeRequest->getMessageCount()
			)
		);
	}

	private function cleanUpClientConsumption( MessageQueueClient $client ): void
	{
		$consumptionInfo = $client->getConsumptionInfo();
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );

			$consumptionInfo->removeMessageId( $messageId );
		}
	}

	protected function whenClientSentAcknowledgement( ClientSentAcknowledgement $event ): void
	{
		$client          = $event->getClient();
		$acknowledgement = $event->getAcknowledgement();

		$this->logger->debug(
			sprintf(
				'<fg:blue>«« Received acknowledgement for message %s from client %s<:fg>',
				$acknowledgement->getMessageId()->toString(),
				$client->getClientId()->toString()
			)
		);

		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$this->logger->debug(
			sprintf(
				'<fg:red>√ Dequeued message %s from queue %s<:fg>',
				$acknowledgement->getMessageId()->toString(),
				$acknowledgement->getQueueName()
			)
		);

		$consumptionInfo = $client->getConsumptionInfo();

		if ( $consumptionInfo->getQueueName()->equals( $acknowledgement->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $acknowledgement->getMessageId() );
		}
	}
}
