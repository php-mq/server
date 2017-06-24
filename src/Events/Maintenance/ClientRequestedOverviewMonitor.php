<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Maintenance;

use PHPMQ\Server\Clients\MaintenanceClient;
use PHPMQ\Server\Commands\StartMonitor;
use PHPMQ\Server\Interfaces\CarriesEventData;

/**
 * Class ClientRequestedOverviewMonitor
 * @package PHPMQ\Server\Events\Maintenance
 */
final class ClientRequestedOverviewMonitor implements CarriesEventData
{
	/** @var MaintenanceClient */
	private $maintenanceClient;

	/** @var StartMonitor */
	private $startMonitorCommand;

	public function __construct( MaintenanceClient $maintenanceClient, StartMonitor $startMonitorCommand )
	{
		$this->maintenanceClient   = $maintenanceClient;
		$this->startMonitorCommand = $startMonitorCommand;
	}

	public function getMaintenanceClient(): MaintenanceClient
	{
		return $this->maintenanceClient;
	}

	public function getStartMonitorCommand(): StartMonitor
	{
		return $this->startMonitorCommand;
	}
}
