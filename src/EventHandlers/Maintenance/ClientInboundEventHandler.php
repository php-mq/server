<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\EventHandlers\Maintenance;

use PHPMQ\Server\EventHandlers\AbstractEventHandler;
use PHPMQ\Server\Events\Maintenance\ClientRequestedMonitor;
use PHPMQ\Server\Events\Maintenance\ClientRequestedQueueMonitor;

/**
 * Class ClientInboundEventHandler
 * @package PHPMQ\Server\EventHandlers\Maintenance
 */
final class ClientInboundEventHandler extends AbstractEventHandler
{
	protected function getAcceptedEvents(): array
	{
		return [
			ClientRequestedMonitor::class,
			ClientRequestedQueueMonitor::class,
		];
	}

	protected function whenClientRequestedMonitor( ClientRequestedMonitor $event ): void
	{
		$client = $event->getMaintenanceClient();
		$this->logger->debug( sprintf( 'Maintenance client %s requested monitor.', $client->getClientId() ) );
	}

	protected function whenClientRequestedQueueMonitor( ClientRequestedQueueMonitor $event ): void
	{
		$client = $event->getMaintenanceClient();
		$this->logger->debug(
			sprintf(
				'Maintenance client %s requested monitor for queue: %s',
				$client->getClientId(),
				$event->getShowQueueCommand()->getQueueName()
			)
		);
	}
}
