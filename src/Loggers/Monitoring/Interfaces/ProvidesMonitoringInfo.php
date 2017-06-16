<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers\Monitoring\Interfaces;

use PHPMQ\Server\Types\QueueInfo;
use PHPMQ\Server\Types\QueueName;

/**
 * Interface ProvidesMonitoringInfo
 * @package PHPMQ\Server\Loggers\Monitoring\Interfaces
 */
interface ProvidesMonitoringInfo
{
	public function getConnectedClientsCount(): int;

	public function getQueueCount(): int;

	public function getQueueInfos(): iterable;

	public function getQueueInfo( QueueName $queueName ): QueueInfo;
}
