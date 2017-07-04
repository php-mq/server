<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Protocol\Messages\MessageE2C;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Streams\Constants\ChunkSize;
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

	public function __construct( StoresMessages $storage, ConsumptionPool $consumptionPool )
	{
		$this->storage         = $storage;
		$this->consumptionPool = $consumptionPool;
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream->getStreamId() );
		if ( !$consumptionInfo->canConsume() )
		{
			return;
		}

		$messages = $this->storage->getUndispatched(
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

			$stream->writeChunked( $messageE2C->toString(), ChunkSize::WRITE );

			$this->storage->markAsDispached( $messageE2C->getQueueName(), $messageE2C->getMessageId() );

			$consumptionInfo->addMessageId( $messageE2C->getMessageId() );
		}
	}
}
