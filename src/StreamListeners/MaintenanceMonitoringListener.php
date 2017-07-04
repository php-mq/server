<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\StreamListeners;

use PHPMQ\Server\Endpoint\Interfaces\ListensForStreamActivity;
use PHPMQ\Server\Endpoint\Interfaces\TracksStreams;
use PHPMQ\Server\Endpoint\Interfaces\TransfersData;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Monitoring\ServerMonitor;
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

	/** @var ServerMonitor */
	private $serverMonitor;

	public function __construct( IdentifiesQueue $queueName, ServerMonitor $serverMonitor )
	{
		$this->queueName     = $queueName;
		$this->serverMonitor = $serverMonitor;
	}

	public function handleStreamActivity( TransfersData $stream, TracksStreams $loop ) : void
	{
		$this->serverMonitor->refresh( $this->queueName, $stream );
	}
}
