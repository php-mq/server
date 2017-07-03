<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\MessageQueue;

use PHPMQ\Server\Clients\ConsumptionPool;
use PHPMQ\Server\Clients\Types\ClientId;
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
		$stream   = $event->getStream();
		$clientId = new ClientId( (string)$stream );

		$this->serverMonitoringInfo->addConnectedClient( $clientId );

		$this->logger->debug( 'New message queue client connected: ' . $clientId );
	}

	protected function whenClientDisconnected( ClientDisconnected $event ) : void
	{
		$stream   = $event->getStream();
		$clientId = new ClientId( (string)$stream );

		$consumptionInfo = $this->consumptionPool->getConsumptionInfo( $stream );
		$queueName       = $consumptionInfo->getQueueName();
		$messageIds      = $consumptionInfo->getMessageIds();

		foreach ( $messageIds as $messageId )
		{
			$this->storage->markAsUndispatched( $queueName, $messageId );
			$this->serverMonitoringInfo->markMessageAsUndispatched( $queueName, $messageId );
		}

		$this->consumptionPool->removeConsumptionInfo( $stream );

		$this->serverMonitoringInfo->removeConnectedClient( $clientId );

		$this->logger->debug( 'Message queue client disconnected: ' . $clientId );
	}
}
