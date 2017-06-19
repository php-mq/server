<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\MessageQueueClient;
use PHPMQ\Server\Events\MessageQueueClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueueClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueueClientSentMessageC2E;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;

/**
 * Class ClientMessageReceivedEventHandler
 * @package PHPMQ\Server\EventHandlers
 */
final class ClientMessageReceivedEventHandler extends AbstractEventHandler
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
			MessageQueueClientSentMessageC2E::class,
			MessageQueueClientSentConsumeResquest::class,
			MessageQueueClientSentAcknowledgement::class,
		];
	}

	protected function whenMessageQueueClientSentMessageC2E( MessageQueueClientSentMessageC2E $event ) : void
	{
		$messageC2E   = $event->getMessageC2E();
		$storeMessage = new Message( MessageId::generate(), $messageC2E->getContent() );

		$this->storage->enqueue( $messageC2E->getQueueName(), $storeMessage );
	}

	protected function whenMessageQueueClientSentConsumeResquest( MessageQueueClientSentConsumeResquest $event ) : void
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

	private function cleanUpClientConsumption( MessageQueueClient $client ) : void
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

	protected function whenMessageQueueClientSentAcknowledgement( MessageQueueClientSentAcknowledgement $event ) : void
	{
		$client          = $event->getClient();
		$acknowledgement = $event->getAcknowledgement();

		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$consumptionInfo = $client->getConsumptionInfo();

		if ( $consumptionInfo->getQueueName()->equals( $acknowledgement->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $acknowledgement->getMessageId() );
		}
	}
}
