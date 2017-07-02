<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
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
			ClientSentMessageC2E::class,
			ClientSentConsumeResquest::class,
			ClientSentAcknowledgement::class,
		];
	}

	protected function whenClientSentMessageC2E( ClientSentMessageC2E $event ) : void
	{
		$messageC2E   = $event->getMessageC2E();
		$storeMessage = new Message( MessageId::generate(), $messageC2E->getContent() );

		$this->storage->enqueue( $messageC2E->getQueueName(), $storeMessage );

		$this->serverMonitoringInfo->addMessage( $messageC2E->getQueueName(), $storeMessage );

		$this->logger->debug( sprintf( '<fg:green>√ Queued message with ID %s<:fg>', $storeMessage->getMessageId() ) );
	}

	protected function whenClientSentConsumeResquest( ClientSentConsumeResquest $event ) : void
	{
		$stream         = $event->getStream();
		$clientId       = new ClientId( (string)$stream );
		$consumeRequest = $event->getConsumeRequest();

		$this->logger->debug( 'Consume request received from client ' . $clientId );
		$this->logger->debug( '- For queue name: ' . $consumeRequest->getQueueName() );
		$this->logger->debug( '- Message count: ' . $consumeRequest->getMessageCount() );
	}

	protected function whenClientSentAcknowledgement( ClientSentAcknowledgement $event ) : void
	{
		$stream          = $event->getStream();
		$clientId        = new ClientId( (string)$stream );
		$acknowledgement = $event->getAcknowledgement();

		$this->logger->debug(
			sprintf(
				'<fg:blue>«« Received acknowledgement for message %s from client %s<:fg>',
				$acknowledgement->getMessageId()->toString(),
				$clientId->toString()
			)
		);

		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$this->serverMonitoringInfo->removeMessage(
			$acknowledgement->getQueueName(),
			$acknowledgement->getMessageId()
		);

		$this->logger->debug(
			sprintf(
				'<fg:red>√ Dequeued message %s from queue %s<:fg>',
				$acknowledgement->getMessageId()->toString(),
				$acknowledgement->getQueueName()
			)
		);
	}
}
