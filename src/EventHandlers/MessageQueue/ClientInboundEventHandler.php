<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessage;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\StreamListeners\MessageQueueConsumeListener;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Stream\Interfaces\TransfersData;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\MessageQueue
 */
final class ClientInboundEventHandler extends AbstractEventHandler
{
	/** @var StoresMessages */
	private $storage;

	/** @var ConsumptionPool */
	private $consumptionPool;

	/** @var CollectsServerMonitoringInfo */
	private $serverMonitoringInfo;

	public function __construct(
		StoresMessages $storage,
		ConsumptionPool $consumptionPool,
		CollectsServerMonitoringInfo $serverMonitoringInfo
	)
	{
		$this->storage              = $storage;
		$this->consumptionPool      = $consumptionPool;
		$this->serverMonitoringInfo = $serverMonitoringInfo;
	}

	protected function getAcceptedEvents() : array
	{
		return [
			ClientSentMessage::class,
			ClientSentConsumeResquest::class,
			ClientSentAcknowledgement::class,
		];
	}

	protected function whenClientSentMessage( ClientSentMessage $event ) : void
	{
		$messageClientToServer = $event->getMessageClientToServer();
		$storeMessage          = new Message( MessageId::generate(), $messageClientToServer->getContent() );

		$this->storage->enqueue( $messageClientToServer->getQueueName(), $storeMessage );

		$this->serverMonitoringInfo->addMessage( $messageClientToServer->getQueueName(), $storeMessage );

		$this->logger->debug( sprintf( '<fg:green>√ Queued message with ID %s<:fg>', $storeMessage->getMessageId() ) );
	}

	protected function whenClientSentConsumeResquest( ClientSentConsumeResquest $event ) : void
	{
		$stream         = $event->getStream();
		$consumeRequest = $event->getConsumeRequest();

		$this->logger->debug( 'Consume request received from client ' . $stream->getStreamId() );
		$this->logger->debug( '- For queue name: ' . $consumeRequest->getQueueName() );
		$this->logger->debug( '- Message count: ' . $consumeRequest->getMessageCount() );

		$this->cleanUpConsumptionInfo( $stream );

		$consumptionInfo = new ConsumptionInfo( $consumeRequest->getQueueName(), $consumeRequest->getMessageCount() );
		$this->consumptionPool->setConsumptionInfo( $stream->getStreamId(), $consumptionInfo );

		$loop = $event->getLoop();

		$loop->addWriteStream(
			$stream,
			new MessageQueueConsumeListener(
				$this->storage,
				$this->consumptionPool,
				$this->serverMonitoringInfo
			)
		);
	}

	private function cleanUpConsumptionInfo( TransfersData $stream ) : void
	{
		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream->getStreamId() );
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );
			$this->serverMonitoringInfo->markMessageAsUndispatched( $queueName, $messageId );
		}

		$this->consumptionPool->removeConsumptionInfo( $stream->getStreamId() );
	}

	protected function whenClientSentAcknowledgement( ClientSentAcknowledgement $event ) : void
	{
		$stream          = $event->getStream();
		$acknowledgement = $event->getAcknowledgement();

		$this->logger->debug(
			sprintf(
				'<fg:blue>«« Received acknowledgement for message %s from client %s<:fg>',
				$acknowledgement->getMessageId()->toString(),
				$stream->getStreamId()->toString()
			)
		);

		$this->storage->dequeue( $acknowledgement->getQueueName(), $acknowledgement->getMessageId() );

		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream->getStreamId() );
		if ( $consumptionInfo->getQueueName()->equals( $acknowledgement->getQueueName() ) )
		{
			$consumptionInfo->removeMessageId( $acknowledgement->getMessageId() );
		}

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
