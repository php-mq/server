<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionInfo;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Clients\Types\ClientId;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Events\MessageQueue\ClientSentAcknowledgement;
use PHPMQ\Server\Events\MessageQueue\ClientSentConsumeResquest;
use PHPMQ\Server\Events\MessageQueue\ClientSentMessageC2E;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
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

		$this->cleanUpConsumptionInfo( $stream );

		$consumptionInfo = new ConsumptionInfo( $consumeRequest->getQueueName(), $consumeRequest->getMessageCount() );
		$this->consumptionPool->setConsumptionInfo( $stream, $consumptionInfo );

		$loop = $event->getLoop();
		$loop->addPeriodicStreamTimer(
			$stream,
			0.1,
			$this->getConsumeListener( $consumptionInfo, $event->getLoop() )
		);
	}

	private function cleanUpConsumptionInfo( $stream ) : void
	{
		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream );
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );
			$this->serverMonitoringInfo->markMessageAsUndispatched( $queueName, $messageId );
		}

		$this->consumptionPool->removeConsumptionInfo( $stream );
	}

	private function getConsumeListener( ProvidesConsumptionInfo $consumptionInfo, TracksStreams $loop ) : \Closure
	{
		$storage = $this->storage;

		return function ( $stream ) use ( $consumptionInfo, $storage, $loop )
		{
			if ( !$consumptionInfo->canConsume() )
			{
				return;
			}

			$messages = $storage->getUndispatched(
				$consumptionInfo->getQueueName(),
				$consumptionInfo->getMessageCount()
			);

			foreach ( $messages as $message )
			{
				$messageE2C = new MessageE2C(
					$message->getMessageId(),
					$consumptionInfo->getQueueName(),
					$message->getContent()
				);

				if ( !@fwrite( $stream, $messageE2C->toString() ) )
				{
					foreach ( $consumptionInfo->getMessageIds() as $messageId )
					{
						$storage->markAsUndispatched( $consumptionInfo->getQueueName(), $messageId );
						$consumptionInfo->removeMessageId( $messageId );
					}

					$loop->removeStream( $stream );
					break;
				}

				$storage->markAsDispached( $consumptionInfo->getQueueName(), $message->getMessageId() );
				$consumptionInfo->addMessageId( $message->getMessageId() );
			}
		};
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

		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream );
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
