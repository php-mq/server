<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Protocol\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\StreamListeners\Interfaces\RefreshesMonitoringInformation;
use PHPMQ\Stream\Interfaces\TransfersData;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MaintenanceMonitoringListener
 * @package PHPMQ\Server\StreamListeners
 */
class MaintenanceMonitoringListener implements ListensForStreamActivity
{
	use LoggerAwareTrait;

	/** @var IdentifiesQueue */
	private $queueName;

	/** @var RefreshesMonitoringInformation */
	private $serverMonitor;

	public function __construct( IdentifiesQueue $queueName, RefreshesMonitoringInformation $serverMonitor )
	{
		$this->queueName     = $queueName;
		$this->serverMonitor = $serverMonitor;
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$this->serverMonitor->refresh( $this->queueName, $stream );
	}
}
