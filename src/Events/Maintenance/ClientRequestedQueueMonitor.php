<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\ShowQueue;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedQueueMonitor
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedQueueMonitor implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var ShowQueue */
	private $showQueueCommand;

	public function __construct( MaintenanceClient $maintenanceClient, ShowQueue $showQueueCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->showQueueCommand  = $showQueueCommand;
	}

	public function getMaintenanceClient(): MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getShowQueueCommand(): ShowQueue
	{
		return $this->showQueueCommand;
	}
}
