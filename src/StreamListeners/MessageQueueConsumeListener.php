<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Protocol\Messages\MessageServerToClient;
use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Streams\Constants\ChunkSize;
use PHPMQ\Server\Streams\Exceptions\WriteTimedOutException;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageQueueConsumeListener
 * @package PHPMQ\Server\StreamListeners
 */
final class MessageQueueConsumeListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

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

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream->getStreamId() );

		if ( !$consumptionInfo->canConsume() )
		{
			return;
		}

		try
		{
			$this->sendMessagesToConsumer( $stream, $consumptionInfo );
		}
		catch ( WriteTimedOutException $e )
		{
			$loop->removeWriteStream( $stream );
		}
	}

	private function sendMessagesToConsumer( TransfersData $stream, ProvidesConsumptionInfo $consumptionInfo ) : void
	{
		$messages = $this->storage->getUndispatched(
			$consumptionInfo->getQueueName(),
			$consumptionInfo->getMessageCount()
		);

		foreach ( $messages as $message )
		{
			$messageServerToClient = new MessageServerToClient(
				$message->getMessageId(),
				$consumptionInfo->getQueueName(),
				$message->getContent()
			);

			$this->sendMessageToConsumer( $messageServerToClient, $stream );

			$consumptionInfo->addMessageId( $messageServerToClient->getMessageId() );
		}
	}

	private function sendMessageToConsumer( MessageServerToClient $message, TransfersData $stream ) : void
	{
		$stream->writeChunked( $message->toString(), ChunkSize::WRITE );

		$this->storage->markAsDispached(
			$message->getQueueName(),
			$message->getMessageId()
		);

		$this->serverMonitoringInfo->markMessageAsDispatched(
			$message->getQueueName(),
			$message->getMessageId()
		);
	}
}
