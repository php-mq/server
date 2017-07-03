<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\EventHandlers\Interfaces\CollectsServerMonitoringInfo;
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
			ClientConnected::class,
			ClientDisconnected::class,
		];
	}

	protected function whenClientConnected( ClientConnected $event ) : void
	{
		$stream = $event->getStream();

		$this->serverMonitoringInfo->addConnectedClient( $stream->getStreamId() );

		$this->logger->debug( 'New message queue client connected: ' . $stream->getStreamId() );
	}

	protected function whenClientDisconnected( ClientDisconnected $event ) : void
	{
		$stream = $event->getStream();

		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream->getStreamId() );
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );
			$this->serverMonitoringInfo->markMessageAsUndispatched( $queueName, $messageId );
		}

		$this->consumptionPool->removeConsumptionInfo( $stream->getStreamId() );

		$this->serverMonitoringInfo->removeConnectedClient( $stream->getStreamId() );

		$this->logger->debug( 'Message queue client disconnected: ' . $stream->getStreamId() );
	}
}
