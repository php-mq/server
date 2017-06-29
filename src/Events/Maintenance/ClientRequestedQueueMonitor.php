<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\ShowQueueCommand;
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

	/** @var ShowQueueCommand */
	private $showQueueCommand;

	public function __construct( MaintenanceClient $maintenanceClient, ShowQueueCommand $showQueueCommand )
	{
		$this->maintenanceClient = $maintenanceClient;
		$this->showQueueCommand  = $showQueueCommand;
	}

	public function getMaintenanceClient(): MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getShowQueueCommand(): ShowQueueCommand
	{
		return $this->showQueueCommand;
	}
}
