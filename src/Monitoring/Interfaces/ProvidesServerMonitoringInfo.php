<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Monitoring\Interfaces;

use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\Types\QueueInfo;

/**
 * Interface ProvidesServerMonitoringInfo
 * @package PHPMQ\Server\Monitoring\Interfaces
 */
interface ProvidesServerMonitoringInfo
{
	public function getStartTime();

	public function getConnectedClientsCount() : int;

	public function getQueueCount() : int;

	public function getMaxQueueSize() : int;

	public function getQueueInfos() : \Generator;

	public function getQueueInfo( IdentifiesQueue $queueName ) : QueueInfo;
}
