<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\StartMonitorCommand;
use PHPMQ\Server\Events\Interfaces\ProvidesMaintenanceClient;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedOverviewMonitor
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedOverviewMonitor implements CarriesEventData, ProvidesMaintenanceClient
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var StartMonitorCommand */
	private $startMonitorCommand;

	public function __construct( MaintenanceClient $maintenanceClient, StartMonitorCommand $startMonitorCommand )
	{
		$this->maintenanceClient   = $maintenanceClient;
		$this->startMonitorCommand = $startMonitorCommand;
	}

	public function getMaintenanceClient(): MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getStartMonitorCommand(): StartMonitorCommand
	{
		return $this->startMonitorCommand;
	}
}
